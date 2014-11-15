<?php

/**
 * @file
 * Paragraphs widget implementation for entity reference.
 */

namespace Drupal\paragraphs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Field\FieldItemListInterface;
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
    $entity = $items->getEntity();

    return array('target_id' => $element);
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $element['target_id'];
  }

  /**
   * Validates an element.
   */
  public function elementValidate($element, FormStateInterface $form_state, $form) { }

  /**
   * Gets the entity labels.
   */
  protected function getLabels(FieldItemListInterface $items, $delta) {
    if ($items->isEmpty()) {
      return array();
    }

    $entity_labels = array();

    // Load those entities and loop through them to extract their labels.
    $entities = entity_load_multiple($this->getFieldSetting('target_type'), $this->getEntityIds($items, $delta));

    foreach ($entities as $entity_id => $entity_item) {
      $label = $entity_item->label();
      $key = "$label ($entity_id)";
      // Labels containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $entity_labels[] = $key;
    }
    return $entity_labels;
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

  /**
   * Creates a new entity from a label entered in the autocomplete input.
   *
   * @param string $label
   *   The entity label.
   * @param int $uid
   *   The entity uid.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function createNewEntity($label, $uid) {
    $entity_manager = \Drupal::entityManager();
    $target_type = $this->getFieldSetting('target_type');
    $target_bundles = $this->getSelectionHandlerSetting('target_bundles');

    // Get the bundle.
    if (!empty($target_bundles)) {
      $bundle = reset($target_bundles);
    }
    else {
      $bundles = entity_get_bundles($target_type);
      $bundle = reset($bundles);
    }

    $entity_type = $entity_manager->getDefinition($target_type);
    $bundle_key = $entity_type->getKey('bundle');
    $label_key = $entity_type->getKey('label');

    $entity = $entity_manager->getStorage($target_type)->create(array(
                                                                  $label_key => $label,
                                                                  $bundle_key => $bundle,
                                                                ));

    if ($entity instanceof EntityOwnerInterface) {
      $entity->setOwnerId($uid);
    }

    return $entity;
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
