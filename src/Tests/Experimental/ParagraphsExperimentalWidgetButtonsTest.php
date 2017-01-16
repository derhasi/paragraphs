<?php

namespace Drupal\paragraphs\Tests\Experimental;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests paragraphs experimental widget buttons.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalWidgetButtonsTest extends ParagraphsExperimentalTestBase {

  use FieldUiTestTrait;

  /**
   * Tests the widget buttons of paragraphs.
   */
  public function testWidgetButtons() {
    $this->addParagraphedContentType('paragraphed_test', 'field_paragraphs');

    $this->loginAsAdmin(['create paragraphed_test content', 'edit any paragraphed_test content']);
    // Add a Paragraph type.
    $paragraph_type = 'text_paragraph';
    $this->addParagraphsType($paragraph_type);
    $this->addParagraphsType('text');

    // Add a text field to the text_paragraph type.
    static::fieldUIAddNewField('admin/structure/paragraphs_type/' . $paragraph_type, 'text', 'Text', 'text_long', [], []);
    $edit = [
      'fields[field_paragraphs][type]' => 'paragraphs',
    ];
    $this->drupalPostForm('admin/structure/types/manage/paragraphed_test/form-display', $edit, t('Save'));
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'field_paragraphs_text_paragraph_add_more');

    // Create a node with a Paragraph.
    $text = 'recognizable_text';
    $edit = [
      'title[0][value]' => 'paragraphs_mode_test',
      'field_paragraphs[0][subform][field_text][0][value]' => $text,
    ];
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_text_paragraph_add_more');
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));
    $node = $this->drupalGetNodeByTitle('paragraphs_mode_test');

    // Test the 'Open' mode.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText($text);

    // Test the 'Closed' mode.
    $this->setParagraphsWidgetMode('paragraphed_test', 'field_paragraphs', 'closed');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Click "Edit" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_1_edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $text);
    $closed_mode_text = 'closed_mode_text';
    // Click "Collapse" button on both paragraphs.
    $edit = ['field_paragraphs[0][subform][field_text][0][value]' => $closed_mode_text];
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_0_collapse');
    $edit = ['field_paragraphs[1][subform][field_text][0][value]' => $closed_mode_text];
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_1_collapse');
    // Verify that we have warning message for each paragraph.
    $this->assertNoUniqueText('You have unsaved changes on this Paragraph item.');
    $this->assertRaw('<div class="paragraphs-collapsed-description">' . $closed_mode_text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertText($closed_mode_text);

    // Test the 'Preview' mode.
    $this->setParagraphsWidgetMode('paragraphed_test', 'field_paragraphs', 'preview');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Click "Edit" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_edit');
    $this->assertFieldByName('field_paragraphs[0][subform][field_text][0][value]', $closed_mode_text);
    $preview_mode_text = 'preview_mode_text';
    $edit = ['field_paragraphs[0][subform][field_text][0][value]' => $preview_mode_text];
    // Click "Collapse" button.
    $this->drupalPostAjaxForm(NULL, $edit, 'field_paragraphs_0_collapse');
    $this->assertText('You have unsaved changes on this Paragraph item.');
    $this->assertText($preview_mode_text);
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertText($preview_mode_text);

    // Test the remove function.
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Click "Remove" button.
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_0_remove');
    $this->drupalPostForm(NULL, [], t('Save and keep published'));
    $this->assertText('paragraphed_test ' . $node->label() . ' has been updated.');
    $this->assertNoText($preview_mode_text);
  }

  /**
   * Sets the Paragraphs widget display mode.
   *
   * @param string $content_type
   *   Content type name where to set the widget mode.
   * @param string $paragraphs_field
   *   Paragraphs field to change the mode.
   * @param string $mode
   *   Mode to be set. ('closed', 'preview' or 'open').
   */
  protected function setParagraphsWidgetMode($content_type, $paragraphs_field, $mode) {
    $this->drupalGet('admin/structure/types/manage/' . $content_type . '/form-display');
    $this->drupalPostAjaxForm(NULL, [], $paragraphs_field . '_settings_edit');
    $this->drupalPostForm(NULL, ['fields[' . $paragraphs_field . '][settings_edit_form][settings][edit_mode]' => $mode], t('Update'));
    $this->drupalPostForm(NULL, [], 'Save');
  }

}
