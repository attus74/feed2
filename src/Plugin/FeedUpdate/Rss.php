<?php

namespace Drupal\feed\Plugin\FeedUpdate;

use Feed;
use Attus\OgReader\Reader;
use Drupal\feed\FeedUpdateBase;

/**
 * Rss
 *
 * @author Attila Németh
 * 13.06.2020
 * 
 * @FeedUpdate(
 *  id = "rss",
 *  feed_type = "rss",
 * )
 * 
 * @see https://packagist.org/packages/dg/rss-php
 */
class Rss extends FeedUpdateBase {
  
  private     $_rss;
  
  /**
   * {@inheritdoc}
   * 
   * 16.06.2020
   */
  public function update(): void 
  {
    $source = $this->configuration['feed']->get('source')
        ->get(0)->get('value')->getValue();
    $this->_rss = Feed::loadRss($source);
    $this->_updateFeed();
    $this->_updateItems();
  }
  
  private function _updateItems(): void
  {
    foreach($this->_rss->item as $item) {
      if (property_exists($item, 'guid')) {
        $guid = (string)$item->guid;
      }
      else {
        $guid = (string)$item->link;
      }
      $guid = mb_substr($guid, 0, 96);
      $existingItem = \Drupal::entityTypeManager()->getStorage('feed_item')
          ->loadByGuid($guid);
      if ($existingItem) {
        $existingItem->set('loaded', time());
        $existingItem->save();
      }
      else {
        $values = [
          'guid' => $guid,
          'feed' => [
            'entity' => $this->configuration['feed'],
          ],
          'title' => (string)$item->title,
          'loaded' => time(),
          'link' => [
            'uri' => (string)$item->link,
          ],
          'date' => (int)$item->timestamp,
        ];
        if (property_exists($item, 'description')) {
          $values['body'] = [
            'value' => (string)$item->description,
            'format' => 'full_html',
          ];
        }
        if (property_exists($item, 'dc:creator')) {
          $values['author'] = $item->{'dc:creator'};
        }
        $reader = new Reader((string)$item->link);
        $reader->read();
        if ($reader->getValue('image')) {
          $values['image'] = $reader->getValue('image');
        }
        $feedItem = \Drupal::entityTypeManager()->getStorage('feed_item')->create($values);
        $feedItem->save();
      }
    }
  }
  
  private function _updateFeed(): void
  {
    $rss = $this->_rss;
    if ($this->configuration['feed']->label() !== $rss->title) {
      $this->configuration['feed']->set('title', $rss->title);
      $this->configuration['feed']->save();
    }
    if ($this->configuration['feed']->get('link')->isEmpty()
        || $this->configuration['feed']->get('link')->get(0)->get('uri')->getValue()
        !== $rss->link) {
      $this->configuration['feed']->set('link', [
        'uri' => $rss->link,
      ]);
      $this->configuration['feed']->save();
    }
    $reader = new Reader($rss->link);
    $reader->read();
    if (!is_null($reader->getValue('image'))) {
      $imageUpdate = FALSE;
      if ($this->configuration['feed']->get('image')->isEmpty()) {
        $imageUpdate = TRUE;
      }
      elseif ($this->configuration['feed']->get('image')
        ->get(0)->get('value')->getValue() !== $reader->getValue('image')) {
        $imageUpdate = TRUE;
      }
      if ($imageUpdate) {
        $this->configuration['feed']->set('image', $reader->getValue('image'));
        $this->configuration['feed']->save();
      }
    }
  }
  
}
