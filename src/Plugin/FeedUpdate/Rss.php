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
  
  /**
   * {@inheritdoc}
   * 
   * 16.06.2020
   */
  public function update(): void 
  {
    $source = $this->configuration['feed']->get('source')
        ->get(0)->get('value')->getValue();
    $rss = Feed::loadRss($source);
    if ($this->configuration['feed']->label() !== $rss->title) {
      $this->configuration['feed']->set('title', $rss->title);
      $this->configuration['feed']->save();
    }
    if ($this->configuration['feed']->get('link')->get(0)->get('uri')->getValue()
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
      elseif ($this->configruation['feed']->get('image')
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
