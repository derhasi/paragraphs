<?php

namespace Drupal\paragraphs_test\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Provides a test feature plugin.
 *
 * @ParagraphsBehavior(
 *   id = "test_dummy_behavior",
 *   label = @Translation("Test dummy plugin"),
 *   description = @Translation("Test dummy plugin"),
 *   weight = 2
 * )
 */
class TestDummyBehavior extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(Paragraph $paragraphs_entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateBehaviorForm(array &$form, FormStateInterface $form_state) { }

  /**
   * {@inheritdoc}
   */
  public function submitBehaviorForm(Paragraph $paragraphs_entity, array $values) {
    $paragraphs_entity->setBehaviorSettings($this->getPluginId(), $values);
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraphs_entity, EntityViewDisplayInterface $display, $view_mode) {
    $build['#attributes']['class'][] = 'dummy_plugin_text';
  }

}
