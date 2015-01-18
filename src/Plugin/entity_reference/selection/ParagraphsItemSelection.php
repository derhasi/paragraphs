<?php

/**
 * @file
 * Contains \Drupal\node\Plugin\Type\selection\NodeSelection.
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
    );

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Item Title'),
      '#description' => t('Label to appear as title on the button as "Add new [title]", this label is translatable'),
      '#default_value' => (!isset($selection_handler_settings['title'])) ? $selection_handler_settings['title'] : PARAGRAPHS_DEFAULT_TITLE,
      '#required' => TRUE,
    );

    $form['title_plural'] = array(
      '#type' => 'textfield',
      '#title' => t('Plural Item Title'),
      '#description' => t('Title in its plural form.'),
      '#default_value' => (!isset($selection_handler_settings['title_plural'])) ? $selection_handler_settings['title_plural'] : PARAGRAPHS_DEFAULT_TITLE_PLURAL,
      '#required' => TRUE,
    );

    $form['edit_mode'] = array(
      '#type' => 'select',
      '#title' => t('Edit mode'),
      '#description' => t('The mode the paragraph item is in by default. Preview will render the paragraph in the preview view mode.'),
      '#options' => array(
        'open' => t('Open'),
        'closed' => t('Closed'),
        'preview' => t('Preview'),
      ),
      '#default_value' => (!empty($selection_handler_settings['edit_mode'])) ? $selection_handler_settings['edit_mode'] : PARAGRAPHS_DEFAULT_EDIT_MODE,
      '#required' => TRUE,
    );

    $form['add_mode'] = array(
      '#type' => 'select',
      '#title' => t('Add mode'),
      '#description' => t('The way to add new paragraphs.'),
      '#options' => array(
        'select' => t('Select List'),
        'button' => t('Buttons'),
      ),
      '#default_value' => (!empty($selection_handler_settings['add_mode'])) ? $selection_handler_settings['add_mode'] : PARAGRAPHS_DEFAULT_ADD_MODE,
      '#required' => TRUE,
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
