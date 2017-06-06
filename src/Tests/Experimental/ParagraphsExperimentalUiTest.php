<?php

namespace Drupal\paragraphs\Tests\Experimental;

use Drupal\field_ui\Tests\FieldUiTestTrait;

/**
 * Tests the Paragraphs user interface.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalUiTest extends ParagraphsExperimentalTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'paragraphs_demo',
  ];

  /**
   * Tests displaying an error message a required paragraph field that is empty.
   */
  public function testEmptyRequiredField() {
    $admin_user = $this->drupalCreateUser([
      'administer node fields',
      'administer paragraph form display',
      'administer node form display',
      'create paragraphed_content_demo content',
      'edit any paragraphed_content_demo content',
    ]);
    $this->drupalLogin($admin_user);

    // Add required field to paragraphed content type.
    $bundle_path = 'admin/structure/types/manage/paragraphed_content_demo';
    $field_title = 'Content Test';
    $field_type = 'field_ui:entity_reference_revisions:paragraph';
    $field_edit = [
      'required' => TRUE,
    ];
    $this->fieldUIAddNewField($bundle_path, 'content', $field_title, $field_type, [], $field_edit);

    // Attempt to create a paragraphed node with an empty required field.
    $title = 'Empty';
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, ['title[0][value]' => $title], t('Save'));
    $this->assertText($field_title . ' field is required');

    // Attempt to create a paragraphed node with only a paragraph in the
    // "remove" mode in the required field.
    $title = 'Remove mode';
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_demo_image_text_add_more');
    $this->drupalPostAjaxForm(NULL, [], 'field_paragraphs_demo_0_remove');
    $this->assertNoText($field_title . ' field is required');
    $this->drupalPostForm(NULL, ['title[0][value]' => $title], t('Save'));
    $this->assertText($field_title . ' field is required');
  }

}
