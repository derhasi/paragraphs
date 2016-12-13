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
 *   id = "test_text_color",
 *   label = @Translation("Test text color behavior plugin"),
 *   description = @Translation("Test text color behavior plugin"),
 *   weight = 1
 * )
 */
class TestTextColorPlugin extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(Paragraph $paragraphs_entity) {
    $form['text_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $paragraphs_entity->getBehaviorSetting($this->getPluginId(), 'text_color', 'blue'),
      '#description' => $this->t("Text color for the paragraph."),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateBehaviorForm(array &$form, FormStateInterface $form_state) {
    $plugin_values = $form_state->getValue($form['#parents']);
    if ($plugin_values['text_color'] != 'blue' && $plugin_values['text_color'] != 'red') {
      $form_state->setError($form, 'The only allowed values are blue and red.');
    }
  }

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
    if ($color = $paragraphs_entity->getBehaviorSetting($this->getPluginId(), 'text_color')) {
      $build['#attributes']['class'][] = $color . '_plugin_text';
      $build['#attached']['library'][] = 'paragraphs_test/drupal.paragraphs_test.color_text';
    }
  }

}
