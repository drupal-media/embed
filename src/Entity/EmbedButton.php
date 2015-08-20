<?php

/**
 * @file
 * Contains \Drupal\embed\Entity\EmbedButton.
 */

namespace Drupal\embed\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\embed\EmbedButtonInterface;

/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "embed_button",
 *   label = @Translation("Embed button"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\embed\Form\EmbedButtonForm",
 *       "edit" = "Drupal\embed\Form\EmbedButtonForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\embed\EmbedButtonListBuilder",
 *   },
 *   admin_permission = "administer embed buttons",
 *   config_prefix = "button",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/content/embed/manage/{embed_button}",
 *     "delete-form" = "/admin/config/content/embed/manage/{embed_button}/delete",
 *     "collection" = "/admin/config/content/embed",
 *   },
 *   config_export = {
 *     "label",
 *     "id",
 *     "embed_type",
 *     "icon_uuid",
 *     "display_plugins",
 *   }
 * )
 */
class EmbedButton extends ConfigEntityBase implements EmbedButtonInterface {

  /**
   * The EmbedButton ID.
   *
   * @var string
   */
  public $id;

  /**
   * Label of EmbedButton.
   *
   * @var string
   */
  public $label;

  /**
   * Selected embed type.
   *
   * @var string
   */
  public $embed_type;

  /**
   * UUID of the button's icon file.
   *
   * @var string
   */
  public $icon_uuid;

  /**
   * Array of allowed display plugins for the entity type.
   *
   * An empty array signifies that all are allowed.
   *
   * @var array
   */
  public $display_plugins;

  /**
   * {@inheritdoc}
   */
  public function getEmbedType() {
    return $this->embed_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbedTypeLabel() {
    if ($definition = $this->embedTypeManager()->getDefinition($this->embed_type, FALSE)) {
      return $definition['label'];
    }
    else {
      return t('Unknown');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIconFile() {
    if ($this->icon_uuid) {
      return $this->entityManager()->loadEntityByUuid('file', $this->icon_uuid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getIconUrl() {
    if ($image = $this->getIconFile()) {
      $style = \Drupal\image\Entity\ImageStyle::load('embed_button');
      return $style->buildUri($image->getFileUri());
    }
    else {
      return file_create_url(drupal_get_path('module', 'embed') . '/js/plugins/drupalembed/embed.png');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedDisplayPlugins() {
    $allowed_display_plugins = array();
    // Include only those plugin ids in result whose value is set.
    foreach ($this->display_plugins as $key => $value) {
      if ($value) {
        $allowed_display_plugins[$key] = $value;
      }
    }
    return $allowed_display_plugins;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Add the file icon entity as dependency if an UUID was specified.
    if ($this->icon_uuid && $file_icon = $this->entityManager()->loadEntityByUuid('file', $this->icon_uuid)) {
      $this->addDependency($file_icon->getConfigDependencyKey(), $file_icon->getConfigDependencyName());
    }

    // Add the embed type plugin as a dependency.
    if ($definition = $this->embedTypeManager()->getDefinition($this->embed_type, FALSE)) {
      $this->addDependency('module', $definition['provider']);
    }

    return $this->dependencies;
  }

  /**
   * Gets the embed type plugin manager.
   *
   * @return \Drupal\embed\EmbedType\EmbedTypeManager
   */
  protected function embedTypeManager() {
    return \Drupal::service('plugin.manager.embed.type');
  }

  /**
   * Gets the embed display plugin manager.
   *
   * @return \Drupal\embed\EmbedType\EmbedTypeManager
   */
  protected function embedDisplayManager() {
    return \Drupal::service('plugin.manager.embed.display');
  }

  /**
   * Gets the file usage service.
   *
   * @return \Drupal\file\FileUsage\FileUsageInterface
   */
  protected function fileUsage() {
    return \Drupal::service('file.usage');
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $new_button_icon_uuid = $this->get('icon_uuid');
    if (isset($this->original)) {
      $old_button_icon_uuid = $this->original->get('icon_uuid');
      if (!empty($old_button_icon_uuid) && $old_button_icon_uuid != $new_button_icon_uuid) {
        if ($file = $this->entityManager()->loadEntityByUuid('file', $old_button_icon_uuid)) {
          $this->fileUsage()->delete($file, 'embed', $this->getEntityTypeId(), $this->id());
        }
      }
    }
    if ($new_button_icon_uuid) {
      if ($file = $this->entityManager()->loadEntityByUuid('file', $new_button_icon_uuid)) {
        $usage = $this->fileUsage()->listUsage($file);
        if (empty($usage['embed'][$this->getEntityTypeId()][$this->id()])) {
          $this->fileUsage()->add($file, 'embed', $this->getEntityTypeId(), $this->id());
        }
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Remove file usage for any button icons.
    foreach ($entities as $entity) {
      /** @var \Drupal\embed\EmbedButtonInterface $entity */
      $icon_uuid = $entity->get('icon_uuid');
      if ($icon_uuid) {
        if ($file = \Drupal::entityManager()->loadEntityByUuid('file', $icon_uuid)) {
          \Drupal::service('file.usage')->delete($file, 'entity_embed', $entity->getEntityTypeId(), $entity->id());
        }
      }
    }
  }

}
