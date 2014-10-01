<?php

/**
 * @file
 * Contains Drupal\account/ParagraphsItemAccessController.
 */

namespace Drupal\paragraphs;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the ParagraphsItem entity.
 *
 * @see \Drupal\paragraphs\Entity\ParagraphsItem.
 */
class ParagraphsItemAccessControlHandler extends EntityAccessControlHandler
{

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return $account->hasPermission('view ParagraphsItem entity');
        break;

      case 'edit':
        return $account->hasPermission('edit ParagraphsItem entity');
        break;

      case 'delete':
        return $account->hasPermission('delete ParagraphsItem entity');
        break;

    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('add ParagraphsItem entity');
  }
}
