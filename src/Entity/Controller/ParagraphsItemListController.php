<?php

/**
 * @file
 * Contains Drupal\paragraphs\Entity\Controller\ParagraphsItemListController.
 */

namespace Drupal\paragraphs\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for ParagraphsItem entity.
 *
 * @ingroup paragraphs
 */
class ParagraphsItemListController extends EntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = t('Paragraphs item id');
    $header['name'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\paragraphs\Entity\ParagraphsItem */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l($this->getLabel($entity),
      'paragraphs_item.list', array(
        'paragraphs_paragraphs_item' => $entity->id(),
      ));
    return $row + parent::buildRow($entity);
  }
}
