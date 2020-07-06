<?php

namespace Drupal\feed\Plugin\FeedUpdate;

use Drupal\feed\FeedUpdateBase;

/**
 * Index
 *
 * @author Attila Németh
 * 06.07.2020
 * 
 * @FeedUpdate(
 *  id = "index",
 *  feed_type = "index",
 * )
 */
class Index extends FeedUpdateBase {
  
  private   $_dom;

  /**
   * {@inheritdoc}
   */
  public function update(): void {
    $url = $this->configuration['feed']->get('source')
        ->get(0)->get('value')->getValue();
    $client = \Drupal::httpClient();
    try {
      $response = $client->request('GET', $url);
    }
    catch (ClientException $ex) {
      \Drupal::logger('Feed')->error('%code @message', [
        '%code' => $ex->getCode(),
        '@message' => $ex->getMessage(),
      ]);
      return;
    }
    libxml_use_internal_errors(true);
    $this->_dom = new \DOMDocument();
    $this->_dom->loadHtml((string)$response->getBody());
    $this->_parseHead();
    $tables = $this->_dom->getElementsByTagName('table');
    for ($i = 0; $i < $tables->length; $i++) {
      if($tables->item($i)->getAttribute('class') == 'art') {
        $this->_parseItem($tables->item($i));
      }
    }
  }
  
  private function _parseHead()
  {
    $titles = $this->_dom->getElementsByTagName('title');
    $title = $titles->item(0)->nodeValue;
    $this->configuration['feed']->set('title', (string)$title);
    $this->configuration['feed']->set('link', [
      'title' => (string)$title,
      'uri' => $this->configuration['feed']->get('source')->get(0)->get('value')->getValue(),
    ]);
    $this->configuration['feed']->save();
  }
  
  private function _parseItem($table)
  {
    $as = $table->getElementsByTagName('a');
    for ($i = 0; $i < $as->length; $i++) {
      if (!empty($as->item($i)->getAttribute('name'))) {
        $postId = $as->item($i)->getAttribute('name');
        $guid = 'post.' . $postId . '.index';
        $existingItem = \Drupal::entityTypeManager()->getStorage('feed_item')
          ->loadByGuid($guid);
        if ($existingItem) {
          $existingItem->set('loaded', time());
          $existingItem->save();
        }
        else {
          $values = [
            'guid' => $guid,
            'loaded' => time(),
            'feed' => [
              'entity' => $this->configuration['feed'],
            ],
          ];
          $tds = $table->getElementsByTagName('td');
          for ($j = 0; $j < $tds->length; $j++) {
            if (preg_match('/art_h_l/', $tds->item($j)->getAttribute('class'))) {
              $tdas = $tds->item($j)->getElementsByTagName('a');
              for ($k = 0; $k < $tdas->length; $k++) {
                if ($tdas->item($k)->getAttribute('rel') == 'bookmark') {
                  $dateString = $tdas->item($k)->getAttribute('title');
                  list($datePart, $timePart) = explode(' ', $dateString);
                  list($year, $month, $day) = explode('.', $datePart);
                  list($hour, $minute, $second) = explode(':', $timePart);
                  $values['date'] = mktime($hour, $minute, $second, $month, $day, $year);
                }
                if (preg_match('/art_owner/', $tdas->item($k)->getAttribute('class'))) {
                  $values['author'] = $tdas->item($k)->nodeValue;
                }
              }
            }
          }
          $divs = $table->getElementsByTagName('div');
          for ($j = 0; $j < $divs->length; $j++) {
            if ($divs->item($j)->getAttribute('class') == 'art_t') {
              $imgs = $divs->item($j)->getElementsByTagName('img');
              $body = $this->_dom->saveHTML($divs->item($j));
              $newImages = [];
              if ($imgs->length > 0) {
                for ($k = 0; $k < $imgs->length; $k++) {
                  $oldImages[$k] = $this->_dom->saveHTML($imgs->item($k));
                  $src = $imgs->item($k)->getAttribute('src');
                  $src = str_replace('/THM', '/MED', $src);
                  $newImages[$k] = '<img src="' . $src . '">';
                }
                $body = str_replace($oldImages, $newImages, $body);
              }
              $trs = $table->getElementsByTagName('tr');
              for ($k = 0; $k < $trs->length; $k++) {
                if ($trs->item($k)->getAttribute('class') == 'art_f') {
                  $tras = $trs->item($k)->getElementsByTagName('a');
                  $qhref = $tras->item(0)->getAttribute('href');
                  $qurl = 'https://forum.index.hu' . str_replace('jumpTree', 'viewArticle', $qhref);
                  $qText = $this->_getQuote($qurl);
                  $body = '<div class="quote">' . $qText . '</div>' . $body;
                }
              }
            }
          }
          $spans = $table->getElementsByTagName('span');
          for ($j = 0; $j < $spans->length; $j++) {
            if ($spans->item($j)->getAttribute('class') == 'art_nr') {
              try {
                $nr = $spans->item($j)->nodeValue;
              }
              catch(\DOMException $ex) {
                \Drupal::logger('Feed')->error('Dom Failed by getting Number: @message', [
                  '@message' =>  $ex->getMessage(),
                ]);
                $nr = -1;
              }
            }
          }
          $values['body'] = [
            'value' => $nr . $body,
            'format' => 'full_html',
          ];
          $values['link'] = [
            'uri' => $this->configuration['feed']->get('source')->get(0)->get('value')->getValue() . '&go=' . $postId . '#' . $postId,
          ];
          $newItem = \Drupal::entityTypeManager()->getStorage('feed_item')->create($values);
          $newItem->save();
        }
      }
    }
    return FALSE;
  }
  
  private function _getQuote(string $url)
  {
    $client = \Drupal::httpClient();
    try {
      $response = $client->request('GET', $url);
      libxml_use_internal_errors(true);
      $dom = new \DOMDocument();
      $dom->loadHtml((string)$response->getBody());
      $divs = $this->_dom->getElementsByTagName('div');
      for ($i = 0; $i < $divs->length; $i++) {
        if ($divs->item($i)->getAttribute('class') == 'art_t') {
          $imgs = $divs->item($i)->getElementsByTagName('img');
          $body = $this->_dom->saveHTML($divs->item($i));
          $newImages = [];
          if ($imgs->length > 0) {
            for ($k = 0; $k < $imgs->length; $k++) {
              $oldImages[$k] = $this->_dom->saveHTML($imgs->item($k));
              $newImages[$k] = '[IMG]';
            }
            $body = str_replace($oldImages, $newImages, $body);
          }
          return $body;
        }
      }
      return '-ERROR-';
    }
    catch (ClientException $ex) {
      \Drupal::logger('Feed')->error('%code @message', [
        '%code' => $ex->getCode(),
        '@message' => $ex->getMessage(),
      ]);
      return FALSE;
    }
  }
  
}
