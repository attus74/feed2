<?php

namespace Drupal\feed;

/**
 * Feed Update Plugin Interface
 *
 * @author Attila Németh
 * @date 13.06.2020
 */
interface FeedUpdateInterface {
  
  /**
   * Feed Update
   */
  public function update(): void;

}