<?php

namespace Drupal\feed\Entity;

use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
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
 *     "storage" = "Drupal\feed\Storage\FeedItem",
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
  
  public function getImageAvatar()
  {
    $image = imagecreatetruecolor(48, 48);
    $feed = $this->get('feed')->first()->entity;
    $avatarUrl = $feed->get('image')->first()->get('value')->getValue();
    try {
      $avatarImage = $this->_getRemoteImage($avatarUrl);
    }
    catch (RequestException $ex) {
      \Drupal::logger('Feed')->warning('Avatar: %code @message', [
        '%code' => $ex->getCode(),
        '@message' => $ex->getMessage(),
      ]);
      return $image;
    } catch (\Exception $ex) {
      var_dump($ex);
      die();
    }
    $w = imagesx($avatarImage);
    $h = imagesy($avatarImage);
    if ($w > $h) {
      $ratio = 48 / $h;
    }
    else {
      $ratio = 48 / $w;
    }
    $avatarWidth = $w * $ratio;
    $avatarHeight = $h * $ratio;
    $dst_x = (48 - $avatarWidth) / 2;
    $dst_y = (48 - $avatarHeight) / 2;
    $src_x = 0;
    $src_y = 0;
    $dst_w = $avatarWidth;
    $dst_h = $avatarHeight;
    $src_w = $w;
    $src_h = $h;
    imagecopyresampled($image, $avatarImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    return $image;
  }
  
  public function getImageStory()
  {
    if ($this->get('image')->isEmpty()) {
      $image = imagecreatetruecolor(1, 1);
      return $image;
    }
    $image = imagecreatetruecolor(800, 450);
    $storyUrl = $this->get('image')->first()->get('value')->getValue();
    try {
      $storyImage = $this->_getRemoteImage($storyUrl);
    }
    catch (RequestException $ex) {
      \Drupal::logger('Feed')->warning('Avatar: %code @message', [
        '%code' => $ex->getCode(),
        '@message' => $ex->getMessage(),
      ]);
      return $image;
    } catch (\Exception $ex) {
      var_dump($ex);
      die();
    }
    $w = imagesx($storyImage);
    $h = imagesy($storyImage);
    if ($w > $h / 9 * 16) {
      $ratio = 450 / $h;
    }
    else {
      $ratio = 800 / $w;
    }
    $storyWidth = $w * $ratio;
    $storyHeight = $h * $ratio;
    $dst_x = (800 - $storyWidth) / 2;
    $dst_y = (450 - $storyHeight) / 2;
    $src_x = 0;
    $src_y = 0;
    $dst_w = $storyWidth;
    $dst_h = $storyHeight;
    $src_w = $w;
    $src_h = $h;
    imagecopyresampled($image, $storyImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    return $image;
  }
  
  private function _getRemoteImage(string $url)
  {
    $response = \Drupal::httpClient()->get($url);
    $contentTypes = $response->getHeader('Content-Type');
    switch($contentTypes[0]) {
      case 'image/jpeg':
        $avatarImage = imagecreatefromjpeg($url);
        break;
      case 'image/png':
        $avatarImage = imagecreatefrompng($url);
        break;
      default:
        throw new \Exception('Unknown Image Type: ' . $contentTypes[0]);
    }
    return $avatarImage;
  }

}