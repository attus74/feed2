<?php

namespace Drupal\feed;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Feed Interface
 */
interface FeedInterface extends ContentEntityInterface {
  
  /**
   * Egy felhasználó hozzáadása a feedhez
   * @param UserInterface $user
   */
  public function addUser(UserInterface $user = NULL): void;
  
  /**
   * Egy felhasználó törlése egy feedtől.
   * @param UserInterface $user
   */
  public function removeUser(UserInterface $user = NULL): void;
  
  /**
   * Feed Update
   */
  public function update(): void;
  
}