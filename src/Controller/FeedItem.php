<?php

namespace Drupal\feed\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;

/**
 * FeedItem
 *
 * @author Attila Németh
 * 05.07.2020
 */
class FeedItem extends ControllerBase {
  
  /**
   * Elem olvasottként jelölése
   * @param string $feedItemId
   * @return Response
   */
  public function setRead(string $feedItemId): Response
  {
    $feedItem = \Drupal::entityTypeManager()->getStorage('feed_item')
        ->loadByUuid($feedItemId);
    $newUsers = [];
    foreach($feedItem->get('user') as $item) {
      $user = $item->entity;
      if ($user->id() !== \Drupal::currentUser()->id()) {
        $newUsers[] = [
          'entity' => $user,
        ];
      }
    }
    $newUsers[] = [
      'target_id' => \Drupal::currentUser()->id(),
    ];
    $feedItem->set('user', $newUsers);
    $feedItem->save();
    return new Response();
  }
  
  /**
   * Hozzáférés ellenőrzése
   * @param string $feedItemId
   * @return AccessResult
   * @throws NotFoundHttpException
   */
  public function feedItemAccess(string $feedItemId): AccessResult
  {
    $feedItem = \Drupal::entityTypeManager()->getStorage('feed_item')
        ->loadByUuid($feedItemId);
    if ($feedItem) {
      if ($feedItem->access('view')) {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
    }
    else {
      throw new NotFoundHttpException;
    }
  }
  
}
