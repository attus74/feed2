<?php

namespace Drupal\feed\Storage;

use Drupal\entity_base\EntityBaseStorage;
use Drupal\feed\FeedInterface;

/**
 * Feed
 *
 * @author Attila Németh
 * 12.06.2020
 */
class Feed extends EntityBaseStorage {
  
  /**
   * Load a Feed by Source URL
   * @param string $source
   */
  public function loadBySource(string $source)
  {
    $feeds = $this->loadByProperties([
      'source' => $source,
    ]);
    if ($feeds && count($feeds)) {
      // It may be only one
      return current($feeds);
    }
    else {
      return NULL;
    }
  }
  
  /**
   * A feed, amit legközelebb kell frissíteni
   * @return boolean
   * 
   * 13.06.2020
   */
  public function loadNextToUpdate()
  {
    $query = \Drupal::entityQuery('feed');
    $update = $query->orConditionGroup()
        ->condition('update', time(), '<=')
        ->condition('update', NULL, 'IS NULL');
    $ids = \Drupal::entityQuery('feed')
            ->condition($update)
            ->sort('update')
            ->pager(1)
            ->execute();
    if (is_array($ids) && count($ids)) {
      $feed = \Drupal::entityTypeManager()->getStorage('feed')->load(current($ids));
      return $feed;
    }
    else {
      return FALSE;
    }
  }
  
}
