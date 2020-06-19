<?php

namespace Drupal\feed;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Zugriffskontrolle
 *
 * @author Attila Németh
 * @date 18.04.2020
 */
class FeedItemAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view feed_item');
      case 'edit':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit feed_item');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete feed_item');
      default:
        throw new \Exception(t('Unknown Operation: @op', [
          '@op' => $op,
        ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create feed_item');
  }

}