<?php

namespace Drupal\Tests\paragraphs\FunctionalJavascript;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\paragraphs\FunctionalJavascript\LoginAdminTrait;

/**
 * Test paragraphs user interface.
 *
 * @group paragraphs
 */
class ParagraphsExperimentalEditPerspectivesUiTest extends JavascriptTestBase {

  use LoginAdminTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'paragraphs_test',
    'paragraphs',
    'field',
    'field_ui',
    'block',
    'link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Test paragraphs user interface.
   */
  public function testEditPerspectives() {

    $this->loginAsAdmin([
      'access content overview',
      'edit behavior plugin settings'
    ]);

    $page = $this->getSession()->getPage();
    $this->drupalGet('admin/structure/paragraphs_type/add');
    $edit = [
      'label' => 'TestPlugin',
      'id' => 'testplugin',
      'behavior_plugins[test_text_color][enabled]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->drupalGet('admin/structure/types/add');
    $edit = [
      'name' => 'TestContent',
      'type' => 'testcontent',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->drupalGet('admin/structure/types/manage/testcontent/fields/add-field');
    $edit = [
      'new_storage_type' => 'field_ui:entity_reference_revisions:paragraph',
      'label' => 'testparagraphfield',
      'field_name' => 'testparagraphfield',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and continue'));
    $edit = [
      'settings[target_type]' => 'paragraph',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));
    $edit = [
      'settings[handler_settings][target_bundles_drag_drop][testplugin][enabled]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save settings'));
    $this->drupalGet('admin/structure/types/manage/testcontent/form-display');
    $page->selectFieldOption('fields[field_testparagraphfield][type]', 'paragraphs');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->drupalGet('node/add/testcontent');
    $this->clickLink('Behavior');
    $style_selector = $page->find('css', '.form-item-field-testparagraphfield-0-behavior-plugins-test-text-color-text-color');
    $this->assertTrue($style_selector->isVisible());
    $this->clickLink('Content');
    $this->assertFalse($style_selector->isVisible());
  }

  /**
   * Test if tabs are visible with no behavior elements.
   */
  public function testTabsVisibility() {
    $this->loginAsAdmin([
      'access content overview',
    ]);

    $page = $this->getSession()->getPage();
    $this->drupalGet('admin/structure/paragraphs_type/add');
    $edit = [
      'label' => 'TestPlugin',
      'id' => 'testplugin',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->drupalGet('admin/structure/types/add');
    $edit = [
      'name' => 'TestContent',
      'type' => 'testcontent',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and manage fields'));
    $this->drupalGet('admin/structure/types/manage/testcontent/fields/add-field');
    $edit = [
      'new_storage_type' => 'field_ui:entity_reference_revisions:paragraph',
      'label' => 'testparagraphfield',
      'field_name' => 'testparagraphfield',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save and continue'));
    $edit = [
      'settings[target_type]' => 'paragraph',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save field settings'));
    $this->drupalPostForm(NULL, NULL, t('Save settings'));
    $this->drupalGet('admin/structure/types/manage/testcontent/form-display');
    $page->selectFieldOption('fields[field_testparagraphfield][type]', 'paragraphs');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->drupalGet('node/add/testcontent');
    $style_selector = $page->find('css', '.paragraphs-tabs');
    $this->assertFalse($style_selector->isVisible());
  }

}
