<?php

namespace Drupal\paragraphs;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a paragraphs entity.
 * @ingroup paragraphs
 */
interface ParagraphInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the parent entity of the paragraph.
   *
   * Preserves language context with translated entities.
   *
   * @return ContentEntityInterface
   *   The parent entity.
   */
  public function getParentEntity();

  /**
   * Returns short summary for paragraph.
   *
   * @param array $options
   *   (optional) Array of additional options, with the following elements:
   *   - 'show_behavior_summary': Whether the summary should contain the
   *     behavior settings. Defaults to TRUE to show behavior settings in the
   *     summary.
   *   - 'depth_limit': Depth limit of how many nested paragraph summaries are
   *     allowed. Defaults to 1 to show nested paragraphs only on top level.
   *
   * @return string
   *   The text without tags.
   */
  public function getSummary(array $options = []);

}
