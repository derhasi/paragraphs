<?php

/**
 * @file
 * Contains Drupal\account/ParagraphsItemAccessController.
 */

namespace Drupal\paragraphs;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

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
    return AccessResult::allowedIfHasPermission($account, 'access paragraphs items overview')->cachePerRole();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'access paragraphs items overview')->cachePerRole();
  }

}
