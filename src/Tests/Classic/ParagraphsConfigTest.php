<?php

namespace Drupal\paragraphs\Tests\Classic;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;

/**
 * Tests paragraphs configuration.
 *
 * @group paragraphs
 */
class ParagraphsConfigTest extends ParagraphsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'content_translation',
  );

  /**
   * Tests adding paragraphs with no translation enabled.
   */
  public function testFieldTranslationDisabled() {
    $this->loginAsAdmin([
      'administer languages',
      'administer content translation',
      'create content translations',
      'translate any entity',
    ]);

    // Add a paragraphed content type.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs_field', 'entity_reference_paragraphs');
    $this->addParagraphsType('paragraph_type_test');
    $this->addParagraphsType('text');

    // Add a second language.
    ConfigurableLanguage::create(['id' => 'de'])->save();

    // Enable translation for paragraphed content type. Do not enable
    // translation for the ERR paragraphs field nor for fields on the
    // paragraph type.
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][paragraphed_test][translatable]' => TRUE,
      'settings[node][paragraphed_test][fields][paragraphs_field]' => FALSE,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, t('Save configuration'));

    // Create a node with a paragraph.
    $this->drupalPostAjaxForm('node/add/paragraphed_test', [], 'paragraphs_field_paragraph_type_test_add_more');
    $edit = ['title[0][value]' => 'paragraphed_title'];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Attempt to add a translation.
    $node = $this->drupalGetNodeByTitle('paragraphed_title');
    $this->drupalGet('node/' . $node->id() . '/translations');
    $this->clickLink(t('Add'));
    // Save the translation.
   $this->drupalPostForm(NULL, [], t('Save (this translation)'));
    $this->assertText('paragraphed_test paragraphed_title has been updated.');
  }

  /**
   * Tests required Paragraphs field.
   */
  public function testRequiredParagraphsField() {
    $this->loginAsAdmin();

    // Add a Paragraph content type and 2 Paragraphs types.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs', 'entity_reference_paragraphs');
    $this->addParagraphsType('paragraph_type_test');
    $this->addParagraphsType('text');

    // Make the paragraphs field required and save configuration.
    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields/node.paragraphed_test.paragraphs');
    $edit = [
      'required' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');
    $this->assertText('Saved paragraphs configuration.');

    // Assert that the field is displayed in the form as required.
    $this->drupalGet('node/add/paragraphed_test');
    $this->assertRaw('<strong class="form-required" data-drupal-selector="edit-paragraphs-title">');
    $edit = [
      'title[0][value]' => 'test_title',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('paragraphs field is required.');
    $this->drupalPostAjaxForm(NULL, [], 'paragraphs_paragraph_type_test_add_more');
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('paragraphed_test test_title has been created.');
  }

  /**
   * Test included Paragraph types.
   */
  public function testIncludedParagraphTypes() {
    $this->loginAsAdmin();
    // Add a Paragraph content type and 2 Paragraphs types.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs', 'entity_reference_paragraphs');
    $this->addParagraphsType('paragraph_type_test');
    $this->addParagraphsType('text');

    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields/node.paragraphed_test.paragraphs');
    $edit = [
      'settings[handler_settings][negate]' => 0,
      'settings[handler_settings][target_bundles_drag_drop][paragraph_type_test][enabled]' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');
    $this->assertText('Saved paragraphs configuration.');

    $this->drupalGet('node/add/paragraphed_test');
    $this->assertText('Add paragraph_type_test');
    $this->assertNoText('Add text');
  }

  /**
   * Test excluded Paragraph types.
   */
  public function testExcludedParagraphTypes() {
    $this->loginAsAdmin();
    // Add a Paragraph content type and 2 Paragraphs types.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs', 'entity_reference_paragraphs');
    $this->addParagraphsType('paragraph_type_test');
    $this->addParagraphsType('text');

    $this->drupalGet('admin/structure/types/manage/paragraphed_test/fields/node.paragraphed_test.paragraphs');
    $edit = [
      'settings[handler_settings][negate]' => 1,
      'settings[handler_settings][target_bundles_drag_drop][text][enabled]' => 1,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');
    $this->assertText('Saved paragraphs configuration.');

    $this->drupalGet('node/add/paragraphed_test');
    $this->assertText('Add paragraph_type_test');
    $this->assertNoText('Add text');
  }

}
