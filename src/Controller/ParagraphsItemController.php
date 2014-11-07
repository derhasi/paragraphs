<?php

/**
 * @file
 * Contains Drupal\paragraphs\Controller\ParagraphsItemController.
 */

namespace Drupal\paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\paragraphs\ParagraphsTypeInterface;

class ParagraphsItemController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays add content links for available content types.
   *
   * Redirects to node/add/[type] if only one content type is available.
   *
   * @return array
   *   A render array for a list of the node types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   redirects to the node add page for that one node type and does not return
   *   at all.
   *
   * @see node_menu()
   */
  public function addPage() {
    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('paragraphs_type')->loadMultiple() as $type) {
      if ($this->entityManager()->getAccessControlHandler('paragraphs_item')->createAccess($type->id)) {
        $content[$type->id] = $type;
      }
    }

    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('paragraphs.item_add', array('paragraphs_type' => $type->id));
    }

    return array(
      '#theme' => 'paragraphs_item_add_list',
      '#content' => $content,
    );
  }

  /**
   * Provides the node submission form.
   *
   * @param \Drupal\paragraphs\ParagraphsTypeInterface $paragraphs_type
   *   The paragraphs type entity for the $paragraphs_type item.
   *
   * @return array
   *   A node submission form.
   */
  public function add(ParagraphsTypeInterface $paragraphs_type) {
    $paragraphs_item = $this->entityManager()->getStorage('paragraphs_item')->create(array(
      'type' => $paragraphs_type->id,
    ));

    $form = $this->entityFormBuilder()->getForm($paragraphs_item);

    return $form;
  }

} 