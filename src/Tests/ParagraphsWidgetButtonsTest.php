<?php

namespace Drupal\paragraphs\Tests;

use Drupal\field_ui\Tests\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;

/**
 * Tests paragraphs widget buttons.
 *
 * @group paragraphs
 */
class ParagraphsWidgetButtonsTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'paragraphs',
    'field',
    'field_ui',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Create paragraphed_test content type.
    $this->drupalCreateContentType([
      'type' => 'paragraphed_test',
      'name' => 'paragraphed_test'
    ]);
    // Place the breadcrumb, tested in fieldUIAddNewField().
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests the widget buttons of paragraphs.
   */
  public function testWidgetButtons() {
    $admin_user = $this->drupalCreateUser([
      'administer nodes',
      'administer content types',
      'administer node fields',
      'administer paragraphs types',
      'create paragraphed_test content',
      'edit any paragraphed_test content',
      'administer node form display',
      'administer paragraphs types',
      'administer paragraph fields',
      'administer paragraph form display',
    ]);
    $this->drupalLogin($admin_user);
    static::fieldUIDeleteField('admin/structure/types/manage/paragraphed_test', 'node.paragraphed_test.body', 'Body', 'paragraphed_test');

    // Add a Paragraph type.
    $paragraph_type = 'text_paragraph';
    $this->addParagraphsType($paragraph_type);

    // Create a Paragraphs field.
    static::fieldUIAddNewField('admin/structure/types/manage/paragraphed_test', 'paragraphs', 'Paragraphs', 'entity_reference_revisions', [
      'settings[target_type]' => 'paragraph',
      'cardinality' => '-1',
    ], []);
    $this->clickLink(t('Manage form display'));
    $this->drupalPostForm(NULL, ['fields[field_paragraphs][type]' => 'entity_reference_paragraphs'], t('Save'));

    // Add a text field to the text_paragraph type.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'text', 'Text', 'text_long', [], []);
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'field_paragraphs_text_paragraph_add_more');

    // Create a node with a Paragraph.
    $text = 'recognizable_text';
    $edit = [
      'title[0][value]' => 'paragraphs_mode_test',
      'field_paragraphs[0][subform][field_text][0][value]' => $text,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle('paragraphs_mode_test');

    // Test the 'Open' mode.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText($text);

    // Test the 'Closed' mode.
    $this->setParagraphsWidgetMode('paragraphed_test', 'paragraphs', 'closed');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Click "Edit" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $text);
    $closed_mode_text = 'closed_mode_text';
    $edit = ['field_paragraphs[0][subform][field_text][0][value]' => $closed_mode_text];
    // Click "Collapse" button.
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_0_collapse');
    $this->assertText('Warning: this content must be saved to reflect changes on this Paragraph item.');
    $this->assertNoText($closed_mode_text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertText($closed_mode_text);

    // Test the 'Preview' mode.
    $this->setParagraphsWidgetMode('paragraphed_test', 'paragraphs', 'preview');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Click "Edit" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $closed_mode_text);
    $preview_mode_text = 'preview_mode_text';
    $edit = ['field_paragraphs[0][subform][field_text][0][value]' => $preview_mode_text];
    // Click "Collapse" button.
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_0_collapse');
    $this->assertText('Warning: this content must be saved to reflect changes on this Paragraph item.');
    $this->assertText($preview_mode_text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertText($preview_mode_text);

    // Test the remove/restore function.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertText($preview_mode_text);
    // Click "Remove" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_remove');
    $this->assertText('Deleted Paragraph type: text_paragraph');
    // Click "Restore" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_restore');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $preview_mode_text);
    $restore_text = 'restore_text';
    $edit = ['field_paragraphs[0][subform][field_text][0][value]' => $restore_text];
    $this->drupalPostForm(NULL, $edit, t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertText($restore_text);

    // Test the remove/confirm remove function.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertText($restore_text);
    // Click "Remove" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_remove');
    $this->assertText('Deleted Paragraph type: text_paragraph');
    // Click "Confirm Removal" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_confirm_remove');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertNoText($restore_text);
  }

  /**
   * Set the Paragraphs widget display mode.
   *
   * @param string $content_type
   *   Content type name where to set the widget mode.
   * @param string $paragraphs_field
   *   Paragraphs field to change the mode.
   * @param string $mode
   *   Mode to be set.
   */
  protected function setParagraphsWidgetMode($content_type, $paragraphs_field, $mode) {
    $this->drupalGet('admin/structure/types/manage/' . $content_type . '/form-display');
    $this->drupalPostAjaxForm(NULL, [], 'field_' . $paragraphs_field . '_settings_edit');
    $this->drupalPostForm(NULL, ['fields[field_' . $paragraphs_field . '][settings_edit_form][settings][edit_mode]' => $mode], t('Update'));
    $this->drupalPostForm(NULL, [], 'Save');
  }

  /**
   * Adds a Paragraphs type.
   *
   * @param string $paragraphs_type
   *   Paragraph type name used to create.
   */
  protected function addParagraphsType($paragraphs_type) {
    $this->drupalGet('admin/structure/paragraphs_type/add');
    $edit = ['label' => $paragraphs_type, 'id' => $paragraphs_type];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
  }

}
