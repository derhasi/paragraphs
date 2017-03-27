<?php

namespace Drupal\paragraphs\Tests\Classic;

use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Tests paragraphs types.
 *
 * @group paragraphs
 */
class ParagraphsTypesTest extends ParagraphsTestBase {

  /**
   * Tests the deletion of Paragraphs types.
   */
  public function testRemoveTypesWithContent() {
    $this->loginAsAdmin();

    // Add a Paragraphed test content.
    $this->addParagraphedContentType('paragraphed_test', 'paragraphs');
    $this->addParagraphsType('paragraph_type_test');
    $this->addParagraphsType('text');

    // Attempt to delete the content type not used yet.
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->clickLink(t('Delete'));
    $this->assertText('This action cannot be undone.');
    $this->clickLink(t('Cancel'));

    // Add a test node with a Paragraph.
    $this->drupalGet('node/add/paragraphed_test');
    $this->drupalPostAjaxForm(NULL, [], 'paragraphs_paragraph_type_test_add_more');
    $this->drupalPostForm(NULL, ['title[0][value]' => 'test_node'], t('Save and publish'));
    $this->assertText('paragraphed_test test_node has been created.');

    // Attempt to delete the paragraph type already used.
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->clickLink(t('Delete'));
    $this->assertText('paragraph_type_test Paragraphs type is used by 1 piece of content on your site. You can not remove this paragraph_type_test Paragraphs type until you have removed all from the content.');

  }

  /**
   * Tests the paragraph type icon settings.
   */
  public function testParagraphTypeIcon() {
    $admin_user = $this->drupalCreateUser(['administer paragraphs types']);
    $this->drupalLogin($admin_user);
    // Add the paragraph type with icon.
    $this->drupalGet('admin/structure/paragraphs_type/add');
    $this->assertText('Paragraph type icon');
    $test_files = $this->drupalGetTestFiles('image');
    $fileSystem = \Drupal::service('file_system');
    $edit = [
      'label' => 'Test paragraph type',
      'id' => 'test_paragraph_type_icon',
      'files[icon_file]' => $fileSystem->realpath($test_files[0]->uri),
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->assertText('Saved the Test paragraph type Paragraphs type.');

    // Check if the icon has been saved.
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->assertRaw('image-test.png');
    $this->clickLink('Edit');
    $this->assertText('image-test.png');

    // Tests calculateDependencies method.
    $paragraph_type = ParagraphsType::load('test_paragraph_type_icon');
    $dependencies = $paragraph_type->getDependencies();
    $dependencies_uuid[] = explode(':', $dependencies['content'][0]);
    $this->assertEqual($paragraph_type->get('icon_uuid'), $dependencies_uuid[0][2]);
  }

}
