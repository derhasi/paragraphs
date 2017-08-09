<?php

namespace Drupal\paragraphs\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element for a actions drop button.
 *
 * Usage example:
 *
 * @code
 * $form['actions'] = array(
 *   '#type' => 'paragraph_actions',
 *   '#buttons' => $buttons
 * );
 * $buttons['button'] = array(
 *   '#type' => 'submit',
 * );
 * @endcode
 *
 * @FormElement("paragraph_actions")
 */
class ParagraphActions extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#pre_render' => [
        [$class, 'preRenderParagraphActions'],
      ],
    ];
  }

  /**
   * Pre render callback for #type 'paragraph_actions'.
   *
   * @param array $element
   *   An associative array containing the properties and children of the table
   *   element.
   *
   * @return array
   *   The processed element.
   */
  public static function preRenderParagraphActions(array $element) {
    $element['#attached']['library'][] = 'paragraphs/drupal.paragraphs.actions';

    $element['actions'] = [
      '#type' => 'container',
      '#attributes' => array(
        'class' => 'paragraphs-actions',
      ),
    ];

    // Toggle button.
    $element['actions']['toggle'] = [
      '#type' => 'inline_template',
      '#template' => '<button class="paragraphs-actions-toggle"><span class="visually-hidden">{% trans %}Toggle Actions{% endtrans %}</span></button>',
    ];

    $element['actions']['buttons'] = [
      '#theme' => 'item_list',
      '#attributes' => [
        'class' => [
          'paragraphs-actions-items',
        ],
      ],
    ];
    foreach ($element['#buttons'] as $key => &$button) {
      if (isset($button['#ajax'])) {
        $button = RenderElement::preRenderAjaxForm($button);
      }
      $button['#attributes']['class'][] = 'paragraphs-actions-action';

      $element['actions']['buttons']['#items'][$key] = [
        '#wrapper_attributes' => [
          'class' => [
            'paragraphs-actions-item',
          ],
        ],
        'button' => $button,
      ];
    }

    unset($element['#buttons']);
    return $element;
  }

}
