<?php

/**
 * @file
 * Contains Drupal\paragraphs\Entity\Controller\ParagraphsItemListController.
 */

namespace Drupal\paragraphs\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

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
    $header['type'] = t('Paragraphs item type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\paragraphs\Entity\ParagraphsItem */
    $row['id'] = \Drupal::l($entity->id(),
      new Url('entity.paragraphs_item.canonical', array(
        'paragraphs_item' => $entity->id(),
      )));
    $row['type'] = $entity->getType();
    return $row + parent::buildRow($entity);
  }
}
