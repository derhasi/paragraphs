<?php

/**
 * @file
 * Paragraphs widget implementation for entity reference.
 */

namespace Drupal\paragraphs\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\String;
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
  public static function defaultSettings() {
    return array(
      'title' => PARAGRAPHS_DEFAULT_TITLE,
      'title_plural' => PARAGRAPHS_DEFAULT_TITLE_PLURAL,
      'edit_mode' => PARAGRAPHS_DEFAULT_EDIT_MODE,
      'add_mode' => PARAGRAPHS_DEFAULT_ADD_MODE,
      'form_display_mode' => PARAGRAPHS_DEFAULT_FORM_DISPLAY_MODE,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = array();

    $elements['title'] = array(
      '#type' => 'textfield',
      '#title' => t('Item Title'),
      '#description' => t('Label to appear as title on the button as "Add new [title]", this label is translatable'),
      '#default_value' => $this->getSetting('title'),
      '#required' => TRUE,
    );

    $elements['title_plural'] = array(
      '#type' => 'textfield',
      '#title' => t('Plural Item Title'),
      '#description' => t('Title in its plural form.'),
      '#default_value' => $this->getSetting('title_plural'),
      '#required' => TRUE,
    );

    $elements['edit_mode'] = array(
      '#type' => 'select',
      '#title' => t('Edit mode'),
      '#description' => t('The mode the paragraph item is in by default. Preview will render the paragraph in the preview view mode.'),
      '#options' => array(
        'open' => t('Open'),
        'closed' => t('Closed'),
        'preview' => t('Preview'),
      ),
      '#default_value' => $this->getSetting('edit_mode'),
      '#required' => TRUE,
    );

    $elements['add_mode'] = array(
      '#type' => 'select',
      '#title' => t('Add mode'),
      '#description' => t('The way to add new paragraphs.'),
      '#options' => array(
        'select' => t('Select list'),
        'button' => t('Buttons'),
      ),
      '#default_value' => $this->getSetting('add_mode'),
      '#required' => TRUE,
    );

    $elements['form_display_mode'] = array(
      '#type' => 'select',
      '#options' => \Drupal::entityManager()->getFormModeOptions($this->getFieldSetting('target_type')),
      '#description' => t('The form display mode to use when rendering the paragraph items form.'),
      '#title' => t('Form display mode'),
      '#default_value' => $this->getSetting('form_display_mode'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $summary[] = t('Title: @title', array('@title' => t($this->getSetting('title'))));
    $summary[] = t('Plural title: @title_plural', array('@title_plural' => t($this->getSetting('title_plural'))));

    switch($this->getSetting('edit_mode')) {
      case 'open':
      default:
        $edit_mode = t('Open');
        break;
      case 'closed':
        $edit_mode = t('Closed');
        break;
      case 'preview':
        $edit_mode = t('Preview');
        break;
    }

    switch($this->getSetting('add_mode')) {
      case 'select':
      default:
        $add_mode = t('Select list');
        break;
      case 'button':
        $add_mode = t('Buttons');
        break;
    }

    $summary[] = t('Edit mode: @edit_mode', array('@edit_mode' => $edit_mode));
    $summary[] = t('Add mode: @add_mode', array('@add_mode' => $add_mode));
    $summary[] = t('Form display mode: @form_display_mode', array('@form_display_mode' => $this->getSetting('form_display_mode')));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $parents = $element['#field_parents'];
    $host_entity = $items->getEntity();
    $paragraphs_entity = FALSE;
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $entity_manager = \Drupal::entityManager();
    $target_type = $this->getFieldSetting('target_type');

    $item_mode = isset($widget_state['paragraphs'][$delta]['mode']) ? $widget_state['paragraphs'][$delta]['mode'] : 'edit';

    if ($items[$delta]->entity) {
      $paragraphs_entity = $items[$delta]->entity;

      // We don't have a widget state yet, get from selector settings.
      if (!isset($widget_state['paragraphs'][$delta]['mode'])) {
        $default_edit_mode = $this->getSetting('edit_mode');

        if ($default_edit_mode == 'open') {
          $item_mode = 'edit';
        }
        elseif ($default_edit_mode == 'closed') {
          $item_mode = 'closed';
        }
        elseif ($default_edit_mode == 'preview') {
          $item_mode = 'preview';
        }
      }
    }
    elseif (isset($widget_state['selected_bundle'])) {

      $entity_type = $entity_manager->getDefinition($target_type);
      $bundle_key = $entity_type->getKey('bundle');

      $paragraphs_entity = $entity_manager->getStorage($target_type)->create(array(
        $bundle_key => $widget_state['selected_bundle'],
      ));
      $items->set($delta, $paragraphs_entity);

      $item_mode = 'edit';
    }

    if ($paragraphs_entity) {
      $element_parents = $parents;
      $element_parents[] = $field_name;
      $element_parents[] = $delta;
      $element_parents[] = 'subform';

      $id_prefix = implode('-', array_merge($parents, array($field_name, $delta)));
      $wrapper_id = drupal_html_id($id_prefix . '-item-wrapper');

      $element += array(
        '#type' => 'container',
        '#element_validate' => array(array($this, 'elementValidate')),
        'subform' => array(
          '#type' => 'container',
          '#parents' => $element_parents,
        ),
      );

      $element['#prefix'] = '<div id="' . $wrapper_id . '">';
      $element['#suffix'] = '</div>';

      $item_bundles = $entity_manager->getBundleInfo($target_type);
      if (isset($item_bundles[$paragraphs_entity->bundle()])) {
        $bundle_info = $item_bundles[$paragraphs_entity->bundle()];
        $element['paragraph_bundle_title'] = array(
          '#type' => 'container',
          '#weight' => -1000,
        );
        $element['paragraph_bundle_title']['info'] = array(
          '#markup' => t('!title type: %bundle', array('!title' => t($this->getSetting('title')), '%bundle' => $bundle_info['label'])),
        );

        $element['actions'] = array(
          '#type' => 'actions',
          '#weight' => 9999,
        );

        if ($item_mode == 'edit') {
          // @todo: access check.
          // @todo: fix limit validation errors.
          $element['actions']['remove_button'] = array(
            '#type' => 'submit',
            '#value' => t('Remove !type paragraph', array('!type' => $bundle_info['label'])),
            '#name' => strtr($id_prefix, '-', '_') . '_remove',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'removeItemSubmit')),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'removeItemAjax'),
              'wrapper' => $wrapper_id,
              'effect' => 'fade',
            ),
          );
        }
        elseif ($item_mode == 'preview' || $item_mode == 'closed') {

          // @todo: access check.
          // @todo: fix limit validation errors.
          $element['actions']['edit_button'] = array(
            '#type' => 'submit',
            '#value' => t('Edit !type paragraph', array('!type' => $bundle_info['label'])),
            '#name' => strtr($id_prefix, '-', '_') . '_edit',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'restoreItemSubmit')),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'restoreItemAjax'),
              'wrapper' => $wrapper_id,
              'effect' => 'fade',
            ),
          );
        }
        elseif ($item_mode == 'remove') {
          $element['actions']['remove_button'] = array(
            '#markup' => '<p>' . t('This !title has been removed, press the button below to restore.', array('!title' => t($this->getSetting('title')))) . ' </p><p><em>' . t('Warning: this !title will actually be deleted when you press or "!save" at the bottom of the page!', array('!title' => $this->getSetting('title'), '!save' => t('Save'))) . '</em></p>',
          );

          // @todo: fix limit validation errors.
          $element['actions']['restore_button'] = array(
            '#type' => 'submit',
            '#value' => t('Restore'),
            '#name' => strtr($id_prefix, '-', '_') . '_restore',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'restoreItemSubmit')),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'restoreItemAjax'),
              'wrapper' => $wrapper_id,
              'effect' => 'fade',
            ),
          );

          // @todo: confirm delete button.
        }
      }

      $display = EntityFormDisplay::collectRenderDisplay($paragraphs_entity, $this->getSetting('form_display_mode'));

      if ($item_mode == 'edit') {

        // @todo: access check.
        $display->buildForm($paragraphs_entity, $element['subform'], $form_state);
      }
      elseif ($item_mode == 'preview') {
        // @todo: access check.
        $element['subform'] = array();
        $element['preview'] = entity_view($paragraphs_entity, 'preview', $paragraphs_entity->language()->getId());
      }
      elseif ($item_mode == 'closed') {
        $element['subform'] = array();
      }
      else {
        $element['subform'] = array();
      }

      $widget_state['paragraphs'][$delta] = array(
        'entity' => $paragraphs_entity,
        'display' => $display,
        'mode' => $item_mode,
      );

      $widget_state['items'] = $items;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }

    return $element;
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
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $parents = $form['#parents'];
    $field_state = static::getWidgetState($parents, $field_name, $form_state);

    $max = $field_state['items_count'];
    $is_multiple = TRUE;

    $title = String::checkPlain($this->fieldDefinition->getLabel());
    $description = $this->fieldFilterXss(\Drupal::token()->replace($this->fieldDefinition->getDescription()));

    $elements = array();

    if ($max > 0) {
      for ($delta = 0; $delta < $max; $delta++) {
        // For multiple fields, title and description are handled by the wrapping
        // table.
        $element = array(
          '#title' => $is_multiple ? '' : $title,
          '#description' => $is_multiple ? '' : $description,
        );
        $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

        if ($element) {
          // Input field for the delta (drag-n-drop reordering).
          if ($is_multiple) {
            // We name the element '_weight' to avoid clashing with elements
            // defined by widget.
            $element['_weight'] = array(
              '#type' => 'weight',
              '#title' => t('Weight for row @number', array('@number' => $delta + 1)),
              '#title_display' => 'invisible',
              // Note: this 'delta' is the FAPI #type 'weight' element's property.
              '#delta' => $max,
              '#default_value' => $items[$delta]->_weight ?: $delta,
              '#weight' => 100,
            );
          }

          $elements[$delta] = $element;
        }
      }
    }

    $entity_manager = \Drupal::entityManager();
    $target_type = $this->getFieldSetting('target_type');
    $bundles = $entity_manager->getBundleInfo($target_type);
    $options = array();

    foreach ($bundles as $machine_name => $bundle) {
      if (!count($this->getSelectionHandlerSetting('target_bundles'))
        || in_array($machine_name, $this->getSelectionHandlerSetting('target_bundles'))) {
        $options[$machine_name] = $bundle['label'];
      }
    }

    if ($max > 0) {
      $elements += array(
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => TRUE,
        '#required' => $this->fieldDefinition->isRequired(),
        '#title' => $title,
        '#description' => $description,
        '#max_delta' => $max-1,
      );
    } else {

      // @todo: properize this.
      $add_text = 'No !title_multiple have been added yet. Select a !title type and press the button below to add one.';
      $element_text = '<label>' . $title . "</label>";
      $element_text .= '<p><em>' . t($add_text, array('!title_multiple' => t($this->getSetting('title_plural')), '!title' => t($this->getSetting('title')))) . '</em></p>';
      $element_text .= $description ? '<div class="description">' . implode('', $description->getAll()) . '</div>' : '';

      $elements += array(
        '#type' => 'container',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => TRUE,
        '#max_delta' => $max-1,
        'text' => array(
          '#markup' => $element_text,
        ),
      );
    }

    // Add 'add more' button, if not working with a programmed form.
    if (($max < $cardinality || $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) && !$form_state->isProgrammed()) {
      $id_prefix = implode('-', array_merge($parents, array($field_name)));
      $wrapper_id = drupal_html_id($id_prefix . '-add-more-wrapper');
      $elements['#prefix'] = '<div id="' . $wrapper_id . '">';
      $elements['#suffix'] = '</div>';

      $elements['add_more'] = array(
        '#type' => 'container',
      );

      $elements['add_more']['add_more_select'] = array(
        '#type'    => 'select',
        '#options' => $options,
        '#title'   => t('Paragraph type'),
        '#label_display' => 'hidden',
      );


      // @todo: access check.
      // @todo: button mode.
      // @todo: fix limit validation errors.
      $elements['add_more']['add_more_button'] = array(
        '#type' => 'submit',
        '#name' => strtr($id_prefix, '-', '_') . '_add_more',
        '#value' => t('Add another item'),
        '#attributes' => array('class' => array('field-add-more-submit')),
        '#limit_validation_errors' => array(array_merge($parents, array($field_name))),
        '#submit' => array(array(get_class($this), 'addMoreSubmit')),
        '#ajax' => array(
          'callback' => array(get_class($this), 'addMoreAjax'),
          'wrapper' => $wrapper_id,
          'effect' => 'fade',
        ),
      );
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
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    if ($widget_state['items_count'] < $element['#cardinality'] || $element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $widget_state['items_count']++;
    }

    $widget_state['selected_bundle'] = $values['add_more']['add_more_select'];

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

  public static function removeItemAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    $element['#prefix'] = '<div class="ajax-new-content">' . (isset($element['#prefix']) ? $element['#prefix'] : '');
    $element['#suffix'] = (isset($element['#suffix']) ? $element['#suffix'] : '') . '</div>';

    unset($element['_weight']);

    return $element;
  }

  public static function removeItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    $delta = array_slice($button['#array_parents'], -3, -2);
    $delta = $delta[0];

    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $widget_state['paragraphs'][$delta]['mode'] = 'remove';

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

  public static function restoreItemAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -2));

    $element['#prefix'] = '<div class="ajax-new-content">' . (isset($element['#prefix']) ? $element['#prefix'] : '');
    $element['#suffix'] = (isset($element['#suffix']) ? $element['#suffix'] : '') . '</div>';

    unset($element['_weight']);

    return $element;
  }

  public static function restoreItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    $delta = array_slice($button['#array_parents'], -3, -2);
    $delta = $delta[0];

    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $widget_state['paragraphs'][$delta]['mode'] = 'edit';

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    return $element;
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

  /**
   * {@inheritdoc}
   */
  public function elementValidate($element, FormStateInterface $form_state, $form) {
    $field_name = $this->fieldDefinition->getName();
    $widget_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
    $delta = $element['#delta'];

    $entity = $widget_state['paragraphs'][$delta]['entity'];
    $display = $widget_state['paragraphs'][$delta]['display'];

    // Only extract/validate values when we are in edit mode.
    if ($widget_state['paragraphs'][$delta]['mode'] == 'edit') {
      $display->extractFormValues($entity, $element['subform'], $form_state);
      $display->validateFormValues($entity, $element['subform'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Don't do entity saving when we have validation erors.
    if (count($form_state->getErrors())) {
      return $values;
    }

    $field_name = $this->fieldDefinition->getName();
    $widget_state = static::getWidgetState($form['#parents'], $field_name, $form_state);

    foreach ($values as $delta => &$item) {
      if (isset($item['subform'])
        && isset($widget_state['paragraphs'][$item['_original_delta']]['entity'])
        && $widget_state['paragraphs'][$item['_original_delta']]['mode'] != 'remove') {
        $paragraphs_entity = $widget_state['paragraphs'][$item['_original_delta']]['entity'];
        $paragraphs_entity->save();
        $item['target_id'] = $paragraphs_entity->id();
      }
      // If our mode is remove don't save or reference this entity.
      // @todo: Maybe we should actually delete it here?
      elseif($widget_state['paragraphs'][$item['_original_delta']]['mode'] == 'remove') {
        $item['target_id']  = NULL;
      }
    }
    return $values;
  }
}
