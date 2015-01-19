<?php

/**
 * @file
 * Contains \Drupal\paragraphs\Plugin\entity_reference\selection\ParagraphsItemSelection.
 */

namespace Drupal\paragraphs\Plugin\entity_reference\selection;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\entity_reference\Plugin\entity_reference\selection\SelectionBase;
use Drupal\Core\Url;

/**
 * Default plugin implementation of the Entity Reference Selection plugin.
 *
 * @EntityReferenceSelection(
 *   id = "paragraphs",
 *   label = @Translation("Paragraphs"),
 *   group = "paragraphs",
 *   entity_types = {"paragraphs_item"},
 *   weight = 0,
 *   deriver = "Drupal\entity_reference\Plugin\Derivative\SelectionBase"
 * )
 */
class ParagraphsItemSelection extends SelectionBase {

  /**
   * {@inheritdoc}
   */
  public static function settingsForm(FieldDefinitionInterface $field_definition) {
    $form = array();

    $entity_manager = \Drupal::entityManager();
    $entity_type_id = $field_definition->getSetting('target_type');
    $selection_handler_settings = $field_definition->getSetting('handler_settings') ?: array();
    $entity_type = $entity_manager->getDefinition($entity_type_id);
    $bundles = $entity_manager->getBundleInfo($entity_type_id);

    // Merge-in default values.
    $selection_handler_settings += array(
      'target_bundles' => array(),
      'add_mode' => PARAGRAPHS_DEFAULT_ADD_MODE,
      'edit_mode' => PARAGRAPHS_DEFAULT_EDIT_MODE,
      'title' => PARAGRAPHS_DEFAULT_TITLE,
      'title_plural' => PARAGRAPHS_DEFAULT_TITLE_PLURAL,
    );

    $bundle_options = array();
    foreach ($bundles as $bundle_name => $bundle_info) {
      $bundle_options[$bundle_name] = $bundle_info['label'];
    }

    $target_bundles_title = t('Paragraph types');

    $form['target_bundles'] = array(
      '#type' => 'checkboxes',
      '#title' => $target_bundles_title,
      '#options' => $bundle_options,
      '#default_value' => (!empty($selection_handler_settings['target_bundles'])) ? $selection_handler_settings['target_bundles'] : array(),
      '#required' => FALSE,
      '#size' => 6,
      '#multiple' => TRUE,
      '#element_validate' => array('_entity_reference_element_validate_filter'),
      '#description' => t('The paragraph types that are allowed to be created in this field. Select none to allow all paragraph types.')
    );

    if (!count($bundle_options)) {
      $form['allowed_bundles_explain'] = array(
        '#type' => 'markup',
        '#markup' => t('You did not add any paragraph bundles yet, click !here to add one.', array('!here' => \Drupal::l(t('here'), new Url('paragraphs.type_add', array()))))
      );
    }

    return $form;
  }

}
