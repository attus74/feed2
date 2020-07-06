<?php

namespace Drupal\feed\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\feed\FeedInterface;

/**
 * Entity Type Feed
 *
 * @author Attila Németh
 * 18.04.2020
 *
 * @ContentEntityType(
 *   id = "feed",
 *   label = @Translation("Feed"),
 *   handlers = {
 *     "storage" = "Drupal\feed\Storage\Feed",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\feed\Controller\FeedList",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\feed\Form\FeedForm",
 *       "edit" = "Drupal\feed\Form\FeedForm",
 *       "delete" = "Drupal\feed\Form\FeedDeleteForm",
 *     },
 *     "access" = "Drupal\feed\FeedAccess",
 *   },
 *   base_table = "feed",
 *   admin_permission = "administer feed",

 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",

 *   },
 *   links = {
 *     "canonical" = "/feed/{feed}",
 *     "add-form" = "/feed/add",
 *     "edit-form" = "/feed/{feed}/edit",
 *     "delete-form" = "/feed/{feed}/delete",
 *     "collection" = "/feed",
 *   },
 *   field_ui_base_route = "feed.settings",
 * )
 */
class Feed extends ContentEntityBase implements FeedInterface {

  /**
   * {@inheritdoc}
   *
   * This method may need a manual edit
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
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Feed label'))
      ->setSetting('max_length', 128)
      ->setReadOnly(TRUE);
    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('Feed Type'))
      ->setSetting('max_length', 16);
    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setDescription(t('Feed Source URL'))
      ->setSetting('max_length', 256);
    $fields['update'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Next Update'))
      ->setDescription(t('Timestamp of Next Update'));
    return $fields;
  }
  
  /**
   * {@inheritdoc}
   * 
   * 13.06.2020
   */
  public function addUser(UserInterface $user = NULL): void 
  {
    if (is_null($user)) {
      $user = \Drupal::entityTypeManager()->getStorage('user')
          ->load(\Drupal::currentUser()->id());
    }
    $userExists = FALSE;
    $newUsers = [];
    foreach($this->get('user') as $item) {
      $newUsers[] = [
        'entity' => $item->entity,
      ];
      if ($item->entity->id() == $user->id()) {
        $userExists = TRUE;
      }
    }
    if (!$userExists) {
      $newUsers[] = [
        'entity' => $user,
      ];
    }
    $this->set('user', $newUsers);
    $this->save();
  }
  
  /**
   * {@inheritdoc}
   * 
   * 13.06.2020
   */
  public function removeUser(UserInterface $user = NULL): void
  {
    if (is_null($user)) {
      $user = \Drupal::entityTypeManager()->getStorage('user')
          ->load(\Drupal::currentUser()->id());
    }
    $newUsers = [];
    foreach($this->get('user') as $item) {
      if ($item->entity->id() != $user->id()) {
        $newUsers[] = [
          'entity' => $item->entity,
        ];
      }
    }
    $this->set('user', $newUsers);
    $this->save();
  }
  
  /**
   * {@inheritdoc}
   * 
   * 16.06.2020
   */
  public function update(): void
  {
    $this->set('update', time() + 3600);
    $this->save();
    $pluginType = \Drupal::service('plugin.manager.feed_update');
    $definitions = $pluginType->getDefinitions();
    foreach($definitions as $definition) {
      if ($definition['feed_type'] == $this->get('type')->get(0)->get('value')->getValue()) {
        $plugin = \Drupal::service('plugin.manager.feed_update')
            ->createInstance($definition['id'], [
          'feed' => $this,
        ]);
        $plugin->update();
        $this->set('update', time() + 450);
        $this->save();
      }
    }
  }

}