<?php

namespace Drupal\Tests\paragraphs\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Tests the ParagraphsType entity hasEnabledBehaviourPlugin functionality.
 *
 * @group paragraphs
 */
class ParagraphsTypeHasEnabledBehaviourPluginTest extends KernelTestBase {


  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'paragraphs',
    'user',
    'paragraphs_test',
  ];

  /**
   * ParagraphsType entity build in setUp()
   *
   * @var ParagraphsType
   */
  protected $paragraphsType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('paragraph');
    \Drupal::moduleHandler()->loadInclude('paragraphs', 'install');

    // Create a paragraph with an enabled and disabled plugin.
    $this->paragraphsType = ParagraphsType::create([
      'label' => 'test_text',
      'id' => 'test_text',
      'behavior_plugins' => [
        'test_text_color' => [
          'enabled' => TRUE,
        ],
        'test_dummy_behavior' => [
          'enabled' => FALSE,
        ],
      ],
    ]);
    $this->paragraphsType->save();
  }

  /**
   * Tests the behavior settings API.
   */
  public function testValidPluginIds() {
    $this->assertTrue($this->paragraphsType->hasEnabledBehaviourPlugin('test_text_color'));
    $this->assertFalse($this->paragraphsType->hasEnabledBehaviourPlugin('test_dummy_behavior'));
  }

  /**
   * Test that invalid plugin id's return false.
   */
  public function testInvalidPluginId() {
    $this->assertFalse($this->paragraphsType->hasEnabledBehaviourPlugin('i_do_not_exist'));
  }

}
