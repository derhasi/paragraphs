<?php

namespace Drupal\paragraphs\Tests\Classic;

use Drupal\simpletest\WebTestBase;

/**
 * Tests asymmetric translation of paragraphs.
 *
 * @group paragraphs
 */
class ParagraphsAssymetricTranslationTest extends WebTestBase {

  public static $modules = [
    'node',
    'paragraphs_demo',
    'content_translation',
    'block',
  ];

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\user\UserInterface $entity
   */
  protected $admin_user;

  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');

    $this->admin_user = $this->drupalCreateUser(
      [
        'administer site configuration',
        'administer nodes',
        'create paragraphed_content_demo content',
        'edit any paragraphed_content_demo content',
        'delete any paragraphed_content_demo content',
        'administer paragraph form display',
        'administer node form display',
        'administer paragraph fields',
        'administer content translation',
        'translate any entity',
        'create content translations',
        'administer languages',
        'administer content types',
      ]
    );

    $this->drupalLogin($this->admin_user);

    // Mark the paragraph entities as untranslatable and the paragraph field
    // as translatable.
    $edit = [
      'entity_types[paragraph]' => FALSE,
      'settings[node][paragraphed_content_demo][fields][field_paragraphs_demo]' => TRUE,
      'settings[paragraph][images][translatable]' => FALSE,
      'settings[paragraph][images][settings][language][language_alterable]' => FALSE,
      'settings[paragraph][image_text][translatable]' => FALSE,
      'settings[paragraph][image_text][settings][language][language_alterable]' => FALSE,
      'settings[paragraph][nested_paragraph][translatable]' => FALSE,
      'settings[paragraph][nested_paragraph][settings][language][language_alterable]' => FALSE,
      'settings[paragraph][text][translatable]' => FALSE,
      'settings[paragraph][text][settings][language][language_alterable]' => FALSE,
      'settings[paragraph][text_image][translatable]' => FALSE,
      'settings[paragraph][text_image][settings][language][language_alterable]' => FALSE,
      'settings[paragraph][user][translatable]' => FALSE,
      'settings[paragraph][user][settings][language][language_alterable]' => FALSE,
    ];
    $this->drupalPostForm(
      'admin/config/regional/content-language',
      $edit,
      t('Save configuration')
    );
  }

  /**
   * Test asymmetric translation.
   */
  public function testParagraphsMultilingualFieldTranslation() {
    // Edit widget to classic.
    $this->drupalGet('/admin/structure/types/manage/paragraphed_content_demo/form-display');
    $this->drupalPostForm(NULL, array('fields[field_paragraphs_demo][type]' => 'entity_reference_paragraphs'), t('Save'));

    // Add an English node.
    $this->drupalGet('node/add/paragraphed_content_demo');
    $this->drupalPostForm(NULL, NULL, t('Add Text'));

    $edit = [
      'title[0][value]' => 'Title in english',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Text in english',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    // Translate the node to French.
    $this->clickLink(t('Translate'));
    $this->clickLink(t('Add'), 1);

    $edit = [
      'title[0][value]' => 'Title in french',
      'field_paragraphs_demo[0][subform][field_text_demo][0][value]' => 'Text in french',
      'revision' => TRUE,
      'revision_log[0][value]' => 'french 1',
    ];
    $this->drupalPostForm(
      NULL,
      $edit,
      t('Save and keep published (this translation)')
    );

    $node = $this->drupalGetNodeByTitle('Title in english');

    // Check the english translation.
    $this->drupalGet('node/' . $node->id());
    $this->assertText('Title in english');
    $this->assertText('Text in english');
    $this->assertNoText('Title in french');
    $this->assertNoText('Text in french');

    // Check the french translation.
    $this->drupalGet('fr/node/' . $node->id());
    $this->assertText('Title in french');
    $this->assertText('Text in french');
    $this->assertNoText('Title in english');
    $this->assertNoText('Text in english');

    $select = \Drupal::database()->select('node__field_paragraphs_demo', 'n');
    $select->innerJoin('paragraphs_item', 'p', 'p.id = n.field_paragraphs_demo_target_id');
    $select->addField('p', 'langcode');
    $select->condition('n.entity_id', $node->id());
    $paragraph_langcodes = $select->execute()->fetchCol();

    $this->assertEqual(
      $paragraph_langcodes,
      ['en', 'fr'],
      'Translated paragraphs are separate entities'
    );
  }

}
