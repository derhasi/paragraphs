<?php

/**
 * @file
 * Paragraphs widget implementation for entity reference.
 */

namespace Drupal\paragraphs\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'entity_reference paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "entity_reference_paragraphs",
 *   label = @Translation("Paragraphs"),
 *   description = @Translation("An paragraphs inline form widget."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class InlineParagraphsWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = array();
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];
    $host_entity = $items->getEntity();
    $paragraphs_entity = FALSE;
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    if ($items[$delta]->entity) {
      $paragraphs_entity = $items[$delta]->entity;
    }
    elseif (isset($widget_state['selected_bundle'])) {
      $entity_manager = \Drupal::entityManager();
      $target_type = $this->getFieldSetting('target_type');

      $entity_type = $entity_manager->getDefinition($target_type);
      $bundle_key = $entity_type->getKey('bundle');

      $paragraphs_entity = $entity_manager->getStorage($target_type)->create(array(
        $bundle_key => $widget_state['selected_bundle'],
      ));

      $items[$delta]->setValue($paragraphs_entity);
    }

    if ($paragraphs_entity) {
      $element += array(
        '#type' => 'container',
        'subform' => array(
          '#parents' => array(
            'subform',
          ),
        ),
        '#tree' => TRUE,
      );

      $display = EntityFormDisplay::collectRenderDisplay($paragraphs_entity, 'default');
      $display->buildForm($paragraphs_entity, $element['subform'], $form_state);
    }

    return array('target_id' => $element);
  }

  /**
   * Builds an array of entity IDs for which to get the entity labels.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of a field item in the array of subelements (0, 1, 2, etc).
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds(FieldItemListInterface $items, $delta) {
    $entity_ids = array();

    foreach ($items as $item) {
      $entity_ids[] = $item->target_id;
    }

    return $entity_ids;
  }

  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();

    // Add 'add more select' button and moves the default 'add more' button, if not working with a programmed form.
    if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && !$form_state->isProgrammed()) {
      $bundles = paragraphs_type_get_types();
      $options = array();

      foreach ($bundles as $machine_name => $bundle) {
        if (!count($this->getSelectionHandlerSetting('target_bundles'))
          || in_array($machine_name, $this->getSelectionHandlerSetting('target_bundles'))) {
          $options[$machine_name] = $bundle->label;
        }
      }

      $button = $elements['add_more'];

      $elements['add_more'] = array(
        '#type' => 'container',
      );

      $elements['add_more']['add_more_select'] = array(
        '#type'    => 'select',
        '#options' => $options,
        '#title'   => t('Paragraph type'),
        '#label_display' => 'hidden',
      );

      $elements['add_more']['add_more_button'] = $button;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    // Ensure the widget allows adding additional items.
    if ($element['#cardinality'] != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return;
    }

    // Add a DIV around the delta receiving the Ajax effect.
    $delta = $element['#max_delta'];
    $element[$delta]['#prefix'] = '<div class="ajax-new-content">' . (isset($element[$delta]['#prefix']) ? $element[$delta]['#prefix'] : '');
    $element[$delta]['#suffix'] = (isset($element[$delta]['#suffix']) ? $element[$delta]['#suffix'] : '') . '</div>';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));
    $values = NestedArray::getValue($form_state->getValues(), array_slice($button['#array_parents'], 0, -3));
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    // Increment the items count.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['items_count']++;
    $field_state['selected_bundle'] = $values['add_more']['add_more_select'];

    static::setWidgetState($parents, $field_name, $form_state, $field_state);

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $element['target_id'];
  }

  /**
   * Returns the value of a setting for the entity reference selection handler.
   *
   * @param string $setting_name
   *   The setting name.
   *
   * @return mixed
   *   The setting value.
   */
  protected function getSelectionHandlerSetting($setting_name) {
    $settings = $this->getFieldSetting('handler_settings');
    return isset($settings[$setting_name]) ? $settings[$setting_name] : NULL;
  }

  /**
   * Checks whether a content entity is referenced.
   *
   * @return bool
   */
  protected function isContentReferenced() {
    $target_type = $this->getFieldSetting('target_type');
    $target_type_info = \Drupal::entityManager()->getDefinition($target_type);
    return $target_type_info->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
  }
}
