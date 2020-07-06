<?php

namespace Drupal\feed\Storage;

use Drupal\entity_base\EntityBaseStorage;

/**
 * FeedItem
 *
 * @author Attila Németh
 * 19.06.2020
 */
class FeedItem extends EntityBaseStorage {
  
  /**
   * Feed Item betöltése GUID alapján
   * @param string $guid
   * @return type
   */
  public function loadByGuid(string $guid) 
  {
    $entities = $this->loadByProperties([
      'guid' => $guid,
    ]);
    if (count($entities)) {
      return current($entities);
    }
    else {
      return NULL;
    }
  }
  
  /**
   * Azok a Feed Itemek, amiket a bejelentkezett felhasználó még nem olvasott.
   * @param int $userId
   * @return array
   * 
   * 28.06.2020
   */
  public function loadUnread(int $userId): array
  {
    $allItemIds = \Drupal::entityQuery('feed_item')
                    ->sort('date', 'ASC')
                    ->execute();
    $readItemIds = \Drupal::entityQuery('feed_item')
                    ->condition('user.target_id', $userId)
                    ->execute();
    foreach ($readItemIds as $id) {
      unset($allItemIds[$id]);
    }
    return \Drupal::entityTypeManager()->getStorage('feed_item')
            ->loadMultiple($allItemIds);
  }
  
}
