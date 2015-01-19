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
      'target_bundles_drag_drop' => array(),
      'add_mode' => PARAGRAPHS_DEFAULT_ADD_MODE,
      'edit_mode' => PARAGRAPHS_DEFAULT_EDIT_MODE,
      'title' => PARAGRAPHS_DEFAULT_TITLE,
      'title_plural' => PARAGRAPHS_DEFAULT_TITLE_PLURAL,
    );

    $bundle_options = array();
    $bundle_options_simple = array();

    // Default weight for new items.
    $weight = count($bundles) + 1;

    foreach ($bundles as $bundle_name => $bundle_info) {
      $bundle_options_simple[$bundle_name] = $bundle_info['label'];
      $bundle_options[$bundle_name] = array(
        'label' => $bundle_info['label'],
        'enabled' => isset($selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['enabled']) ? $selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['enabled'] : FALSE,
        'weight' => isset($selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['weight']) ? $selection_handler_settings['target_bundles_drag_drop'][$bundle_name]['weight'] : $weight,
      );
      $weight++;
    }

    // Kept for compatibility with other entity reference widgets.
    $form['target_bundles'] = array(
      '#type' => 'checkboxes',
      '#options' => $bundle_options_simple,
      '#default_value' => isset($selection_handler_settings['target_bundles']) ? $selection_handler_settings['target_bundles'] : array(),
      '#access' => FALSE,
    );

    $form['target_bundles_drag_drop'] = array(
      '#element_validate' => array('paragraphs_bundle_validate'),
      '#type' => 'table',
      '#header' => array(
        t('Bundle'),
        t('Weight'),
      ),
      '#attributes' => array(
        'id' => 'bundles',
      ),
      '#prefix' => '<h5>' . t('Paragraph types') . '</h5>',
      '#suffix' => '<div class="description">' . t('The paragraph types that are allowed to be created in this field. Select none to allow all paragraph types.') .'</div>',
    );

    $form['target_bundles_drag_drop']['#tabledrag'][] = array(
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'bundle-weight',
    );

    uasort($bundle_options, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    $weight_delta = $weight;

    // Default weight for new items.
    $weight = count($bundles) + 1;
    foreach ($bundle_options as $bundle_name => $bundle_info) {
      $form['target_bundles_drag_drop'][$bundle_name] = array(
        '#attributes' => array(
          'class' => array('draggable'),
        ),
      );

      $form['target_bundles_drag_drop'][$bundle_name]['enabled'] = array(
        '#type' => 'checkbox',
        '#title' => $bundle_info['label'],
        '#title_display' => 'after',
        '#default_value' => $bundle_info['enabled'],
      );

      $form['target_bundles_drag_drop'][$bundle_name]['weight'] = array(
        '#type' => 'weight',
        '#default_value' => (int) $bundle_info['weight'],
        '#delta' => $weight_delta,
        '#title' => t('Weight for @bundle bundle', array('@bundle' => $bundle_info['label'])),
        '#title_display' => 'invisible',
        '#attributes' => array(
          'class' => array('bundle-weight', 'bundle-weight-' . $bundle_name),
        ),
      );
      $weight++;
    }

    if (!count($bundle_options)) {
      $form['allowed_bundles_explain'] = array(
        '#type' => 'markup',
        '#markup' => t('You did not add any paragraph bundles yet, click !here to add one.', array('!here' => \Drupal::l(t('here'), new Url('paragraphs.type_add', array()))))
      );
    }

    return $form;
  }
}
