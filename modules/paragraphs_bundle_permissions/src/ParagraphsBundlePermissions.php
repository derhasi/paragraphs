<?php

/**
 * @file
 * Contains \Drupal\paragraphs_bundle_permissions\ParagraphsBundlePermissions.
 */

namespace Drupal\paragraphs_bundle_permissions;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Defines a class containing permission callbacks.
 */
class ParagraphsBundlePermissions {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Returns an array of content permissions.
   *
   * @return array
   */
  public function globalPermissions() {
    return array(
      'bypass paragraphs bundle content access' => array(
        'title' => $this->t('Bypass paragraphs bundle content access control'),
        'description' => $this->t('Is able to administer content for all paragraph bundles'),
      ),
    );
  }

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   */
  public function paragraphBundlePermissions() {
    $perms = array();

    // Generate paragraphs item permissions for all paragraphs types.
    foreach (ParagraphsType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Builds a standard list of node permissions for a given type.
   *
   * @param \Drupal\paragraphs\Entity\ParagraphsType $type
   *   The machine name of the node type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(ParagraphsType $type) {
    $type_id = $type->id();
    $type_params = array('%type_name' => $type->label());

    return array(
      'view paragraph content ' .$type_id => array(
        'title' => $this->t('%type_name: View content', $type_params),
        'description' => t('Is able to view paragraphs content of bundle %type_name', $type_params),
      ),
      'create paragraph content ' . $type_id => array(
        'title' => $this->t('%type_name: Create content', $type_params),
        'description' => t('Is able to create paragraphs content of bundle %type_name', $type_params),
      ),
      'update paragraph content ' . $type_id => array(
        'title' => $this->t('%type_name: Edit content', $type_params),
        'description' => t('Is able to update paragraphs content of bundle %type_name', $type_params),
      ),
      'delete paragraph content ' . $type_id => array(
        'title' => $this->t('%type_name: Delete content', $type_params),
        'description' => t('Is able to delete paragraphs content of bundle %type_name', $type_params),
      ),
    );
  }

}
