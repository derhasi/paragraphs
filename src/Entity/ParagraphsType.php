<?php

/**
 * @file
 * Contains Drupal\paragraphs\Entity\ParagraphsType.
 */

namespace Drupal\paragraphs\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\paragraphs\ParagraphsTypeInterface;

/**
 * Defines the ParagraphsType entity.
 *
 * @ConfigEntityType(
 *   id = "paragraphs_type",
 *   label = @Translation("Paragraphs type"),
 *   handlers = {
 *     "list_builder" = "Drupal\paragraphs\Controller\ParagraphsTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\paragraphs\Form\ParagraphsTypeForm",
 *       "edit" = "Drupal\paragraphs\Form\ParagraphsTypeForm",
 *       "delete" = "Drupal\paragraphs\Form\ParagraphsTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "paragraphs_type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   bundle_of = "paragraphs_item",
 *   links = {
 *     "edit-form" = "paragraphs_type.edit",
 *     "delete-form" = "paragraphs_type.delete"
 *   }
 * )
 */
class ParagraphsType extends ConfigEntityBundleBase implements ParagraphsTypeInterface
{

  /**
   * The ParagraphsType ID.
   *
   * @var string
   */
  public $id;

  /**
   * The ParagraphsType label.
   *
   * @var string
   */
  public $label;

}
