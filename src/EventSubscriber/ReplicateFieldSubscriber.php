<?php

namespace Drupal\paragraphs\EventSubscriber;

use Drupal\replicate\Events\ReplicateEntityFieldEvent;
use Drupal\replicate\Events\ReplicatorEvents;
use Drupal\replicate\Replicator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that handles cloning through the Replicate module.
 */
class ReplicateFieldSubscriber implements EventSubscriberInterface {

  /**
   * Replicates paragraphs when the parent entity is being replicated.
   *
   * @param \Drupal\replicate\Events\ReplicateEntityFieldEvent $event
   */
  public function onClone(ReplicateEntityFieldEvent $event) {
    $field_item_list = $event->getFieldItemList();
    if ($field_item_list->getItemDefinition()->getSetting('target_type') == 'paragraph') {
      foreach ($field_item_list as $field_item) {
        $field_item->entity = \Drupal::service('replicate.replicator')->replicateEntity($field_item->entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // We can't rely on ReplicatorEvents::replicateEntityField() to get the
    // event name as that would create hard dependency to the Replicate module.
    $events['replicate__entity_field__entity_reference_revisions'][] = 'onClone';
    return $events;
  }

}
