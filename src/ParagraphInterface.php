<?php

/**
 * @file
 * Contains Drupal\paragraphs\ParagraphInterface.
 */

namespace Drupal\paragraphs;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a paragraphs entity.
 * @ingroup account
 */
interface ParagraphInterface extends ContentEntityInterface, EntityOwnerInterface
{

  // Add get/set methods for your configuration properties here.
}
