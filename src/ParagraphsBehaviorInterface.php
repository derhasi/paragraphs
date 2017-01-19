<?php

namespace Drupal\paragraphs;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Provides an interface defining a paragraph behavior.
 *
 * A paragraph behavior plugin adds extra functionality to the paragraph such as
 * adding properties and attributes, it can also add extra classes to the render
 * elements so extra styling can be applied.
 */
interface ParagraphsBehaviorInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Builds a behavior perspective for each paragraph based on its type.
   *
   * This method is responsible for building the behavior form for each
   * Paragraph so the user can set special attributes and properties.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraphs_entity
   *   The paragraphs entity.
   *
   * @return array
   *   The fields build array that the plugin creates.
   */
  public function buildBehaviorForm(Paragraph $paragraphs_entity);

  /**
   * Validates the behavior fields form.
   *
   * This method is responsible for validating the data in the behavior fields
   * form and displaying validation messages.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateBehaviorForm(array &$form, FormStateInterface $form_state);

  /**
   * Submit the values taken from the form to store the values.
   *
   * This method is responsible for submitting the data and saving it in the
   * paragraphs entity.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraphs_entity
   *   The paragraphs entity.
   * @param array $values
   *   The values taken from the form.
   */
  public function submitBehaviorForm(Paragraph $paragraphs_entity, array $values);

  /**
   * Adds a default set of helper variables for preprocessors and templates.
   *
   * This preprocess function is the first in the sequence of preprocessing
   * functions that are called when preparing variables of a paragraph.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An array of elements to display in view mode.
   *   - paragraph: The paragraph object.
   *   - view_mode: The view mode.
   */
  public function preprocess(&$variables);

  /**
   * Extends the paragraph render array with behavior.
   *
   * @param array &$build
   *   A renderable array representing the paragraph. The module may add
   *   elements to $build prior to rendering. The structure of $build is a
   *   renderable array as expected by drupal_render().
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $view_mode
   *   The view mode the entity is rendered in.
   *
   * @return array
   *   A render array provided by the plugin.
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode);

  /**
   * Returns if the plugin can be used for the provided paragraphs type.
   *
   * @param \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type
   *   The paragraphs type entity that should be checked.
   *
   * @return bool
   *   TRUE if the formatter can be used, FALSE otherwise.
   */
  public static function isApplicable(ParagraphsType $paragraphs_type);

  /**
   * Returns a short summary for the current behavior settings.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph.
   *
   * @return string[]
   *   The plugin settings.
   */
  public function settingsSummary(Paragraph $paragraph);

}
