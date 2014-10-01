<?php

/**
 * @file
 * Contains Drupal\paragraphs\ParagraphsItemInterface.
 */

namespace Drupal\paragraphs;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a ParagraphsItem entity.
 * @ingroup account
 */
interface ParagraphsItemInterface extends ContentEntityInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
