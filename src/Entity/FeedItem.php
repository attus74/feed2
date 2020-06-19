<?php

namespace Drupal\feed\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\UserInterface;
use Drupal\feed\FeedItemInterface;

/**
 * Entity Type Feed Item
 *
 * @author Attila Németh
 * 18.04.2020
 *
 * @ContentEntityType(
 *   id = "feed_item",
 *   label = @Translation("Feed Item"),
 *   handlers = {
 *     "storage" = "Drupal\entity_base\EntityBaseStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\feed\Controller\FeedItemList",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\feed\Form\FeedItemForm",
 *       "edit" = "Drupal\feed\Form\FeedItemForm",
 *       "delete" = "Drupal\feed\Form\FeedItemDeleteForm",
 *     },
 *     "access" = "Drupal\feed\FeedItemAccess",
 *   },
 *   base_table = "feed_item",
 *   admin_permission = "administer feed_item",

 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",

 *   },
 *   links = {
 *     "canonical" = "/feed/item/{feed_item}",
 *     "add-form" = "/feed/item/add",
 *     "edit-form" = "/feed/item/{feed_item}/edit",
 *     "delete-form" = "/feed/item/{feed_item}/delete",
 *     "collection" = "/feed/item",
 *   },
 *   field_ui_base_route = "feed_item.settings",
 * )
 */
class FeedItem extends ContentEntityBase implements FeedItemInterface {

  /**
   * {@inheritdoc}
   *
   * This method may need a manual edit
   *
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('Entity Id'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('Entity UUID'))
      ->setReadOnly(TRUE);
    $fields['guid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('GUID'))
      ->setDescription(t('General Unique ID'))
      ->setSetting('max_length', 127)
      ->setReadOnly(TRUE);
    $fields['feed'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Feed'))
      ->setDescription(t('Feed Reference'))
      ->setSetting('target_type', 'feed')
      ->setSetting('handler', 'default')
      ->setReadOnly(TRUE);
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Feed Item label'))
      ->setReadOnly(TRUE);
    $fields['date'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Date'))
      ->setDescription(t('Create Date'));
    $fields['loaded'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Loaded'))
      ->setDescription(t('Last Load Date'));
    return $fields;
  }

}