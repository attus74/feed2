<?php

namespace Drupal\feed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Feed Update Annotation
 *
 * @author Attila Németh
 * @date 13.06.2020
 *
 * @Annotation
 */
class FeedUpdate extends Plugin {

  // Plugin ID
  public $id;

  // Feed Type
  public $feed_type;

}