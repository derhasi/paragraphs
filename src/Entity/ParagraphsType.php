<?php

namespace Drupal\paragraphs\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\paragraphs\ParagraphsBehaviorCollection;
use Drupal\paragraphs\ParagraphsBehaviorInterface;
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
 *       "delete" = "Drupal\paragraphs\Form\ParagraphsTypeDeleteConfirm"
 *     }
 *   },
 *   config_prefix = "paragraphs_type",
 *   admin_permission = "administer paragraphs types",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "icon_uuid",
 *     "behavior_plugins",
 *   },
 *   bundle_of = "paragraph",
 *   links = {
 *     "edit-form" = "/admin/structure/paragraphs_type/{paragraphs_type}",
 *     "delete-form" = "/admin/structure/paragraphs_type/{paragraphs_type}/delete",
 *     "collection" = "/admin/structure/paragraphs_type",
 *   }
 * )
 */
class ParagraphsType extends ConfigEntityBundleBase implements ParagraphsTypeInterface, EntityWithPluginCollectionInterface {

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

  /**
   * UUID of the paragraphs type icon file.
   *
   * @var string
   */
  protected $icon_uuid;

  /**
   * The paragraphs type behavior plugins configuration keyed by their id.
   *
   * @var array
   */
  public $behavior_plugins = [];

  /**
   * Holds the collection of behavior plugins that are attached to this
   * paragraphs type.
   *
   * @var \Drupal\paragraphs\ParagraphsBehaviorCollection
   */
  protected $behaviorCollection;

  /**
   * {@inheritdoc}
   */
  public function getIconFile() {
    if ($this->icon_uuid) {
      $files = $this->entityTypeManager()
        ->getStorage('file')
        ->loadByProperties(['uuid' => $this->icon_uuid]);

      if ($files) {
        return array_shift($files);
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBehaviorPlugins() {
    if (!isset($this->behaviorCollection)) {
      $this->behaviorCollection = new ParagraphsBehaviorCollection(\Drupal::service('plugin.manager.paragraphs.behavior'), $this->behavior_plugins);
    }
    return $this->behaviorCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconUrl() {
    if ($image = $this->getIconFile()) {
      return file_create_url($image->getFileUri());
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getBehaviorPlugin($instance_id) {
    return $this->getBehaviorPlugins()->get($instance_id);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Add the file icon entity as dependency if a UUID was specified.
    if ($this->icon_uuid && $file_icon = $this->getIconFile()) {
      $this->addDependency($file_icon->getConfigDependencyKey(), $file_icon->getConfigDependencyName());
    }

    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledBehaviorPlugins() {
    return $this->getBehaviorPlugins()->getEnabled();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return ['behavior_plugins' => $this->getBehaviorPlugins()];
  }

  /**
   * {@inheritdoc}
   */
  public function hasEnabledBehaviorPlugin($plugin_id) {
    $plugins = $this->getBehaviorPlugins();
    if ($plugins->has($plugin_id)) {
      /** @var ParagraphsBehaviorInterface $plugin */
      $plugin = $plugins->get($plugin_id);
      $config = $plugin->getConfiguration();
      return (array_key_exists('enabled', $config) && $config['enabled'] === TRUE);
    }
    return FALSE;
  }

}
