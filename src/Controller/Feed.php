<?php

namespace Drupal\feed\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Attus\JsonApiExtended\JsonApiResponse;

/**
 * Feed Controller
 *
 * @author Attila Németh
 * 20.04.2020
 */
class Feed extends ControllerBase {
  
  /**
   * Struktur-Seite
   */
  public function StructurePage()
  {
    $build = [
      '#markup' => 'HIHI',
    ];
    return $build;
  }
  
  /**
   * Create a Feed, or add the current user to an existing one
   * @return Response
   * 
   * 13.06.2020
   */
  public function createFeed(): Response
  {
    $payload = \Drupal::request()->getContent();
    $args = Json::decode($payload);
    $feed = \Drupal::entityTypeManager()
        ->getStorage('feed')
        ->loadBySource($args['attributes']['source']);
    if (is_null($feed)) {
      $values = [
        'type' => $args['attributes']['type'],
        'source' => $args['attributes']['source'],
      ];
      $feed = \Drupal::entityTypeManager()->getStorage('feed')->create($values);
      $feed->save();
    }
    $feed->addUser();
    return new Response();
  }
  
  /**
   * Egy felhasználó törlése egy feedtől.
   * Ha egy felhasználó se maradt, a feed törlése
   * @param string $feedId
   * @return Response
   * @throws NotFoundHttpException
   */
  public function deleteFeed(string $feedId): Response 
  {
    $feed = \Drupal::entityTypeManager()->getStorage('feed')->loadByUuid($feedId);
    if ($feed) {
      $feed->removeUser();
      if (count($feed->get('user')) == 0) {
        $feed->delete();
      }
      return new Response();
    }
    else {
      throw new NotFoundHttpException;
    }
  }
  
  /**
   * A feedek, amire a felhasználó feliratkozott
   * @return JsonResponse
   * @throws NotFoundHttpException
   * 
   * 13.06.2020
   */
  public function getSubscribedFeeds(): JsonResponse
  {
    $feedIds = \Drupal::entityQuery('feed')
                  ->condition('user.target_id', \Drupal::currentUser()->id())
                  ->execute();
    if (is_array($feedIds) && count($feedIds)) {
      $data = [];
      $feeds = \Drupal::entityTypeManager()->getStorage('feed')
          ->loadMultiple($feedIds);
      $formatter = \Drupal::service('entityjson.formatter');
      foreach($feeds as $feed) {
        if ($feed->access('view')) {
          $formatter->setEntity($feed);
          $formatter->format();
          $data[] = $formatter->getFormatted();
        }
      }
      return new JsonApiResponse($data);
    }
    else {
      throw new NotFoundHttpException;
    }
  }
  
  /**
   * Egy feed frissítése, és az olvasatlan elemek betöltése
   * @return JsonResponse
   * 
   * 26.06.2020
   */
  public function updateNext(): JsonResponse
  {
    $feed = \Drupal::entityTypeManager()->getStorage('feed')->loadNextToUpdate();
//    $feed = \Drupal::entityTypeManager()->getStorage('feed')->load(4);
    if ($feed) {
      $feed->update();
    }
    $data = [];
    $unreadItems = \Drupal::entityTypeManager()->getStorage('feed_item')
        ->loadUnread(\Drupal::currentUser()->id());
    $formatter = \Drupal::service('entityjson.formatter');
    foreach($unreadItems as $feedItem) {
        $formatter->setEntity($feedItem);
        $formatter->format();
        $formatter->addRelationship('feed');
        $f = $formatter->getFormatted();
        $f['meta'] = [
          'image' => [
            'avatar' => Url::fromRoute('feed_item.image.avatar', [
              'feedItemId' => $feedItem->uuid(),
            ], [
              'absolute' => TRUE,
            ])->toString(),
            'story' => Url::fromRoute('feed_item.image.story', [
              'feedItemId' => $feedItem->uuid(),
            ], [
              'absolute' => TRUE,
            ])->toString(),
          ],
        ];
        $data[] = $f;
    }
    return new JsonApiResponse($data);
  }
  
}
