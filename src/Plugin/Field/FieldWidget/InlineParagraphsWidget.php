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
use Drupal\Core\Url;
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
        'dropdown' => t('Dropdown button')
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
      case 'dropdown':
        $add_mode = t('Dropdown button');
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
    $access_control_handler = $entity_manager->getAccessControlHandler($target_type);

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

        $element['top'] = array(
          '#type' => 'container',
          '#weight' => -1000,
          '#attributes' => array(
            'class' => array(
              'paragraph-bundle-top',
            ),
          ),
        );

        $element['top']['paragraph_type_title'] = array(
          '#type' => 'container',
          '#weight' => 0,
          '#attributes' => array(
            'class' => array(
              'paragraph-type-title',
            ),
          ),
        );

        $element['top']['paragraph_type_title']['info'] = array(
          '#markup' => t('!title type: %type', array('!title' => t($this->getSetting('title')), '%type' => $bundle_info['label'])),
        );

        $actions = array();
        $links = array();
        $info = array();

        if ($item_mode == 'edit') {

          $links['remove_button'] = array(
            '#type' => 'submit',
            '#value' => t('Remove'),
            '#name' => strtr($id_prefix, '-', '_') . '_remove',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'removeItemSubmit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'itemAjax'),
              'wrapper' => $widget_state['ajax_wrapper_id'],
              'effect' => 'fade',
            ),
            '#access' => $paragraphs_entity->access('delete'),
            '#prefix' => '<li class="remove">',
            '#suffix' => '</li>',
          );

          $info['edit_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to edit this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('update') && $paragraphs_entity->access('delete'),
          );

          $info['remove_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to remove this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('delete') && $paragraphs_entity->access('update'),
          );

          $info['edit_remove_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to edit or remove this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('update') && !$paragraphs_entity->access('delete'),
          );
        }
        elseif ($item_mode == 'preview' || $item_mode == 'closed') {

          $links['remove_button'] = array(
            '#type' => 'submit',
            '#value' => t('Remove'),
            '#name' => strtr($id_prefix, '-', '_') . '_remove',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'removeItemSubmit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'itemAjax'),
              'wrapper' => $widget_state['ajax_wrapper_id'],
              'effect' => 'fade',
            ),
            '#access' => $paragraphs_entity->access('delete'),
            '#prefix' => '<li class="remove">',
            '#suffix' => '</li>',
          );

          $links['edit_button'] = array(
            '#type' => 'submit',
            '#value' => t('Edit'),
            '#name' => strtr($id_prefix, '-', '_') . '_edit',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'editItemSubmit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'itemAjax'),
              'wrapper' => $widget_state['ajax_wrapper_id'],
              'effect' => 'fade',
            ),
            '#access' => $paragraphs_entity->access('update'),
            '#prefix' => '<li class="edit">',
            '#suffix' => '</li>',
          );

          $info['preview_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to view this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('view'),
          );

          $info['edit_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to edit this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('update') && $paragraphs_entity->access('delete'),
          );

          $info['remove_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to remove this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('delete') && $paragraphs_entity->access('update'),
          );

          $info['edit_remove_button_info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to edit or remove this !title item.', array('!title' => t($this->getSetting('title')))) . '</em>',
            '#access' => !$paragraphs_entity->access('update') && !$paragraphs_entity->access('delete'),
          );
        }
        elseif ($item_mode == 'remove') {
          $info['remove_button_info'] = array(
            '#markup' => '<p>' . t('This !title has been removed, press the button below to restore.', array('!title' => t($this->getSetting('title')))) . ' </p><p><em>' . t('Warning: this !title will actually be deleted when you press "!confirm" or "!save" at the bottom of the page!', array('!title' => $this->getSetting('title'), '!confirm' => t('Confirm removal'), '!save' => t('Save'))) . '</em></p>',
          );

          $actions['restore_button'] = array(
            '#type' => 'submit',
            '#value' => t('Restore'),
            '#name' => strtr($id_prefix, '-', '_') . '_restore',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'restoreItemSubmit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'itemAjax'),
              'wrapper' => $widget_state['ajax_wrapper_id'],
              'effect' => 'fade',
            ),
          );

          $actions['confirm_remove_button'] = array(
            '#type' => 'submit',
            '#value' => t('Confirm removal'),
            '#name' => strtr($id_prefix, '-', '_') . '_confirm_remove',
            '#weight' => 999,
            '#submit' => array(array(get_class($this), 'confirmRemoveItemSubmit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#delta' => $delta,
            '#ajax' => array(
              'callback' => array(get_class($this), 'itemAjax'),
              'wrapper' => $widget_state['ajax_wrapper_id'],
              'effect' => 'fade',
            ),
          );
        }

        if (count($links)) {
          $show_links = 0;
          foreach($links as $link_item) {
            if (!isset($link_item['#access']) || $link_item['#access']) {
              $show_links++;
            }
          }

          if ($show_links > 0) {

            $element['top']['links'] = $links;
            if ($show_links > 1) {
              $element['top']['links']['#theme_wrappers'] = array('dropbutton_wrapper', 'paragraphs_dropbutton_wrapper');
              $element['top']['links']['prefix'] = array(
                '#markup' => '<ul class="dropbutton">',
                '#weight' => 0,
              );
              $element['top']['links']['suffix'] = array(
                '#markup' => '</li>',
                '#weight' => 0,
              );
            }
            else {
              $element['top']['links']['#theme_wrappers'] = array('paragraphs_dropbutton_wrapper');
              foreach($links as $key => $link_item) {
                unset($element['top']['links'][$key]['#prefix']);
                unset($element['top']['links'][$key]['#suffix']);
              }
            }
            $element['top']['links']['#weight'] = 1;
          }
        }

        if (count($info)) {
          $show_info = FALSE;
          foreach($info as $info_item) {
            if (!isset($info_item['#access']) || $info_item['#access']) {
              $show_info = TRUE;
              break;
            }
          }

          if ($show_info) {
            $element['info'] = $info;
            $element['info']['#weight'] = 998;
          }
        }

        if (count($actions)) {
          $show_actions = FALSE;
          foreach($actions as $action_item) {
            if (!isset($action_item['#access']) || $action_item['#access']) {
              $show_actions = TRUE;
              break;
            }
          }

          if ($show_actions) {
            $element['actions'] = $actions;
            $element['actions']['#type'] = 'actions';
            $element['actions']['#weight'] = 999;
          }
        }
      }

      $display = EntityFormDisplay::collectRenderDisplay($paragraphs_entity, $this->getSetting('form_display_mode'));

      if ($item_mode == 'edit') {
        $display->buildForm($paragraphs_entity, $element['subform'], $form_state);
      }
      elseif ($item_mode == 'preview') {
        $element['subform'] = array();
        $element['preview'] = entity_view($paragraphs_entity, 'preview', $paragraphs_entity->language()->getId());
        $element['preview']['#access'] = $paragraphs_entity->access('view');
      }
      elseif ($item_mode == 'closed') {
        $element['subform'] = array();
      }
      else {
        $element['subform'] = array();
      }

      $element['subform']['#attributes']['class'][] = 'paragraphs-subform';
      $element['subform']['#access'] = $paragraphs_entity->access('update');

      if ($item_mode == 'removed') {
        $element['#access'] = FALSE;
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

  public function getAllowedTypes() {

    $return_bundles = array();

    $entity_manager = \Drupal::entityManager();
    $target_type = $this->getFieldSetting('target_type');
    $bundles = $entity_manager->getBundleInfo($target_type);


    // Support for the paragraphs reference type.
    $dragdrop_settings = $this->getSelectionHandlerSetting('target_bundles_drag_drop');
    if ($dragdrop_settings) {
      $drag_drop_settings = $this->getSelectionHandlerSetting('target_bundles_drag_drop');
      $enable_count = 0;

      // Check how much types are enabled as none enabled = all enabled.
      foreach($drag_drop_settings as $bundle_info) {
        if (isset($bundle_info['enabled']) && $bundle_info['enabled']) {
          $enable_count++;
        }
      }


      // Default weight for new items.
      $weight = count($bundles) + 1;
      foreach ($bundles as $machine_name => $bundle) {

        if ((isset($drag_drop_settings[$machine_name]['enabled']) && $drag_drop_settings[$machine_name]['enabled']) || $enable_count === 0) {
          $return_bundles[$machine_name] = array(
            'label' => $bundle['label'],
            'weight' => isset($drag_drop_settings[$machine_name]['weight']) ? $drag_drop_settings[$machine_name]['weight'] : $weight,
          );
          $weight++;
        }
      }
    }
    // Support for other reference types.
    else {
      $weight = 0;
      foreach ($bundles as $machine_name => $bundle) {
        if (!count($this->getSelectionHandlerSetting('target_bundles'))
          || in_array($machine_name, $this->getSelectionHandlerSetting('target_bundles'))) {

          $return_bundles[$machine_name] = array(
            'label' => $bundle['label'],
            'weight' => $weight,
          );

          $weight++;
        }
      }
    }

    uasort($return_bundles, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    return $return_bundles;
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
    $real_item_count = $max;
    $is_multiple = $this->fieldDefinition->getFieldStorageDefinition()->isMultiple();

    $title = String::checkPlain($this->fieldDefinition->getLabel());
    $description = $this->fieldFilterXss(\Drupal::token()->replace($this->fieldDefinition->getDescription()));

    $elements = array();
    $id_prefix = implode('-', array_merge($parents, array($field_name)));
    $wrapper_id = drupal_html_id($id_prefix . '-add-more-wrapper');
    $elements['#prefix'] = '<div id="' . $wrapper_id . '">';
    $elements['#suffix'] = '</div>';

    $field_state['ajax_wrapper_id'] = $wrapper_id;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);

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

          if (isset($element['#access']) && !$element['#access']) {
            $real_item_count--;
          }
          else {
            $elements[$delta] = $element;
          }
        }
      }
    }

    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['real_item_count'] = $real_item_count;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);

    $entity_manager = \Drupal::entityManager();
    $target_type = $this->getFieldSetting('target_type');
    $bundles = $this->getAllowedTypes();
    $access_control_handler = $entity_manager->getAccessControlHandler($target_type);

    $options = array();
    $access_options = array();

    foreach ($bundles as $machine_name => $bundle) {
      if (!count($this->getSelectionHandlerSetting('target_bundles'))
        || in_array($machine_name, $this->getSelectionHandlerSetting('target_bundles'))) {
        $options[$machine_name] = $bundle['label'];

        if ($access_control_handler->createAccess($machine_name)) {
          $access_options[$machine_name] = $bundle['label'];
        }
      }
    }

    if ($real_item_count > 0) {
      $elements += array(
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => $is_multiple,
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
    if (($real_item_count < $cardinality || $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) && !$form_state->isProgrammed()) {
      $elements['add_more'] = array(
        '#type' => 'container',
        '#theme_wrappers' => array('paragraphs_dropbutton_wrapper'),
      );

      if (count($access_options)) {
        if ($this->getSetting('add_mode') == 'button' || ($this->getSetting('add_mode') == 'dropdown' && count($access_options) === 1)) {

          foreach ($access_options as $machine_name => $label) {
            $elements['add_more']['add_more_button_' . $machine_name] = array(
              '#type' => 'submit',
              '#name' => strtr($id_prefix, '-', '_') . $machine_name . '_add_more',
              '#value' => t('Add a !type !title', array('!type' => $label, '!title' => t($this->getSetting('title')))),
              '#attributes' => array('class' => array('field-add-more-submit')),
              '#limit_validation_errors' => array_merge($parents, array($field_name)),
              '#submit' => array(array(get_class($this), 'addMoreSubmit')),
              '#ajax' => array(
                'callback' => array(get_class($this), 'addMoreAjax'),
                'wrapper' => $wrapper_id,
                'effect' => 'fade',
              ),
              '#bundle_machine_name' => $machine_name,
            );
          }
        }
        elseif($this->getSetting('add_mode') == 'dropdown') {
          foreach ($access_options as $machine_name => $label) {
            $elements['add_more']['add_more_button_' . $machine_name] = array(
              '#type' => 'submit',
              '#name' => strtr($id_prefix, '-', '_') . $machine_name . '_add_more',
              '#value' => t('Add a !type !title', array('!type' => $label, '!title' => t($this->getSetting('title')))),
              '#attributes' => array('class' => array('field-add-more-submit')),
              '#limit_validation_errors' => array_merge($parents, array($field_name)),
              '#submit' => array(array(get_class($this), 'addMoreSubmit')),
              '#ajax' => array(
                'callback' => array(get_class($this), 'addMoreAjax'),
                'wrapper' => $wrapper_id,
                'effect' => 'fade',
              ),
              '#bundle_machine_name' => $machine_name,
              '#prefix' => '<li>',
              '#suffix' => '</li>',
            );
          }
          $elements['add_more']['#theme_wrappers'] = array('dropbutton_wrapper', 'paragraphs_dropbutton_wrapper');
          $elements['add_more']['prefix'] = array(
            '#markup' => '<ul class="dropbutton">',
            '#weight' => 0,
          );
          $elements['add_more']['suffix'] = array(
            '#markup' => '</li>',
            '#weight' => 0,
          );
        }
        else {
          $elements['add_more']['add_more_select'] = array(
            '#type'    => 'select',
            '#options' => $options,
            '#title'   => t('!title type', array('!title' => t($this->getSetting('title')))),
            '#label_display' => 'hidden',
          );

          $text = t('Add a !title', array('!title' => t($this->getSetting('title'))));

          if ($real_item_count > 0) {
            $text = t('Add another !title', array('!title' => t($this->getSetting('title'))));
          }

          $elements['add_more']['add_more_button'] = array(
            '#type' => 'submit',
            '#name' => strtr($id_prefix, '-', '_') . '_add_more',
            '#value' => $text,
            '#attributes' => array('class' => array('field-add-more-submit')),
            '#limit_validation_errors' => array_merge($parents, array($field_name)),
            '#submit' => array(array(get_class($this), 'addMoreSubmit')),
            '#ajax' => array(
              'callback' => array(get_class($this), 'addMoreAjax'),
              'wrapper' => $wrapper_id,
              'effect' => 'fade',
            ),
          );
        }
      }
      else {
        if (count($options)) {
          $elements['add_more']['info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You are not allowed to add any of the !title types.', array('!title' => t($this->getSetting('title')))) . '</em>',
          );
        }
        else {
          $elements['add_more']['info'] = array(
            '#type' => 'markup',
            '#markup' => '<em>' . t('You did not add any !title types yet.', array('!title' => t($this->getSetting('title')))) . '</em>',
          );
        }
      }
    }

    $elements['#attached']['library'][] = 'paragraphs/drupal.paragraphs.admin';

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

    if ($widget_state['real_item_count'] < $element['#cardinality'] || $element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $widget_state['items_count']++;
    }

    if (isset($button['#bundle_machine_name'])) {
      $widget_state['selected_bundle'] = $button['#bundle_machine_name'];
    }
    else {
      $widget_state['selected_bundle'] = $values['add_more']['add_more_select'];
    }

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }


  public static function removeItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    $delta = array_slice($button['#array_parents'], -4, -3);
    $delta = $delta[0];

    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $widget_state['paragraphs'][$delta]['mode'] = 'remove';

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

  public static function confirmRemoveItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -3));

    $delta = array_slice($button['#array_parents'], -3, -2);
    $delta = $delta[0];

    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $widget_state['paragraphs'][$delta]['mode'] = 'removed';

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
  }

  public static function editItemSubmit(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    $delta = array_slice($button['#array_parents'], -4, -3);
    $delta = $delta[0];

    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $widget_state = static::getWidgetState($parents, $field_name, $form_state);

    $widget_state['paragraphs'][$delta]['mode'] = 'edit';

    static::setWidgetState($parents, $field_name, $form_state, $widget_state);

    $form_state->setRebuild();
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

  public static function itemAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -4));

    $element['#prefix'] = '<div class="ajax-new-content">' . (isset($element['#prefix']) ? $element['#prefix'] : '');
    $element['#suffix'] = (isset($element['#suffix']) ? $element['#suffix'] : '') . '</div>';

    return $element;
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
      if (isset($widget_state['paragraphs'][$item['_original_delta']]['entity'])
        && $widget_state['paragraphs'][$item['_original_delta']]['mode'] != 'remove') {
        $paragraphs_entity = $widget_state['paragraphs'][$item['_original_delta']]['entity'];
        $paragraphs_entity->save();
        $item['target_id'] = $paragraphs_entity->id();
      }
      // If our mode is remove don't save or reference this entity.
      // @todo: Maybe we should actually delete it here?
      elseif($widget_state['paragraphs'][$item['_original_delta']]['mode'] == 'remove' || $widget_state['paragraphs'][$item['_original_delta']]['mode'] == 'removed') {
        $item['target_id']  = NULL;
      }
    }
    return $values;
  }
}
