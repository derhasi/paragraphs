<?php

namespace Drupal\paragraphs\Tests\Experimental;

/**
 * Tests paragraphs types.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalTypesTest extends ParagraphsExperimentalTestBase {

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
   * Tests the creation of a paragraphs type.
   */
  public function testAddParagraphsType() {
    $this->loginAsAdmin();

    // Go to the Paragraphs types page.
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->assertTitle('Paragraphs types | Drupal');

    // Add a paragraphs type.
    $this->clickLink(t('Add paragraphs type'));
    $this->assertText('Add paragraphs type');
    $this->assertTitle('Add paragraphs type | Drupal');
    $edit = [
      'label' => 'paragraph_type_test',
      'id' => 'paragraph_type_test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->assertTitle('Manage fields | Drupal');
    $this->assertText('Saved the paragraph_type_test Paragraphs type.');

    // Edit the paragraphs type.
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->clickLink(t('Edit'));
    $this->assertTitle('Edit paragraph_type_test paragraph type | Drupal');
    $edit = [
      'label' => 'paragraph_type_renamed_test',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertTitle('Paragraphs types | Drupal');
    $this->assertText('Saved the paragraph_type_renamed_test Paragraphs type.');
  }

}
