<?php

namespace Drupal\feed\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Feed List
 *
 * @author Attila Németh
 * 18.04.2020
 */
class FeedList extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      t('Feed'),
      t('Forrás'),
    ];
    // You can add custom header elements, e.g.:
    // $header['fieldName'] = t('Field Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [
      $entity->toLink()->toString(),
      $entity->get('source')->get(0)->get('value')->getValue(),
    ];
    // You can add custom row elements, e.g.:
    // $row['fieldName'] = $entity->get('fieldName')->get(0)->get('value')->getValue();
    return $row + parent::buildRow($entity);
  }

}