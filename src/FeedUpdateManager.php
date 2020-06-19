<?php

namespace Drupal\feed;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Feed Update Plugin Manager 
 *
 * @author Attila Németh
 * @date 13.06.2020
 */
class FeedUpdateManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/FeedUpdate',
      $namespaces,
      $module_handler,
      'Drupal\feed\FeedUpdateInterface',
      'Drupal\feed\Annotation\FeedUpdate'
    );
    $this->alterInfo('feed_update_info');
    $this->setCacheBackend($cache_backend, 'feed_update_plugins');
  }

}