<?php

namespace Drupal\Tests\paragraphs\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Test trait for Paragraphs JS tests.
 */
trait ParagraphsTestBaseTrait {

  /**
   * Adds a content type with a Paragraphs field.
   *
   * @param string $content_type_name
   *   Content type name to be used.
   * @param string $paragraphs_field_name
   *   (optional) Field name to be used. Defaults to 'field_paragraphs'.
   * @param string $widget_type
   *   (optional) Declares if we use experimental or classic widget.
   *   Defaults to 'paragraphs' for experimental widget.
   *   Use 'entity_reference_paragraphs' for classic widget.
   */
  protected function addParagraphedContentType($content_type_name, $paragraphs_field_name = 'field_paragraphs', $widget_type = 'paragraphs') {
    // Create the content type.
    $node_type = NodeType::create([
      'type' => $content_type_name,
      'name' => $content_type_name,
    ]);
    $node_type->save();

    $this->addParagraphsField($content_type_name, $paragraphs_field_name, 'node', $widget_type);
  }

  /**
   * Adds a Paragraphs field to a given entity type.
   *
   * @param string $entity_type_name
   *   Entity type name to be used.
   * @param string $paragraphs_field_name
   *   Paragraphs field name to be used.
   * @param string $entity_type
   *   Entity type where to add the field.
   * @param string $widget_type
   *   (optional) Declares if we use experimental or classic widget.
   *   Defaults to 'paragraphs' for experimental widget.
   *   Use 'entity_reference_paragraphs' for classic widget.
   */
  protected function addParagraphsField($entity_type_name, $paragraphs_field_name, $entity_type, $widget_type = 'paragraphs') {
    $field_storage = FieldStorageConfig::loadByName($entity_type, $paragraphs_field_name);
    if (!$field_storage) {
      // Add a paragraphs field.
      $field_storage = FieldStorageConfig::create([
        'field_name' => $paragraphs_field_name,
        'entity_type' => $entity_type,
        'type' => 'entity_reference_revisions',
        'cardinality' => '-1',
        'settings' => [
          'target_type' => 'paragraph',
        ],
      ]);
      $field_storage->save();
    }
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $entity_type_name,
      'settings' => [
        'handler' => 'default:paragraph',
        'handler_settings' => ['target_bundles' => NULL],
      ],
    ]);
    $field->save();

    $form_display = EntityFormDisplay::create([
      'targetEntityType' => $entity_type,
      'bundle' => $entity_type_name,
      'mode' => 'default',
      'status' => TRUE,
    ])
      ->setComponent($paragraphs_field_name, ['type' => $widget_type]);
    $form_display->save();

    $view_display = EntityViewDisplay::create([
      'targetEntityType' => $entity_type,
      'bundle' => $entity_type_name,
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent($paragraphs_field_name, ['type' => 'entity_reference_revisions_entity_view']);
    $view_display->save();
  }

  /**
   * Adds a Paragraphs type.
   *
   * @param string $paragraphs_type_name
   *   Paragraph type name used to create.
   */
  protected function addParagraphsType($paragraphs_type_name) {
    $paragraphs_type = ParagraphsType::create([
      'id' => $paragraphs_type_name,
      'label' => $paragraphs_type_name,
    ]);
    $paragraphs_type->save();
  }

}
