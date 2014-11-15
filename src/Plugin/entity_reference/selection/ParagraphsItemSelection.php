<?php

/**
 * @file
 * Contains \Drupal\node\Plugin\Type\selection\NodeSelection.
 */

namespace Drupal\paragraphs\Plugin\entity_reference\selection;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\entity_reference\Plugin\entity_reference\selection\SelectionBase;

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
    $form = parent::settingsForm($field_definition);

    //target_bundles not required default all bundles allowed
    $form['target_bundles']['#required'] = FALSE;

    //remove sort dropdown
    unset($form['sort']);

    return $form;
  }

}
