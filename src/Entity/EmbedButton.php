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
 *   label = @Translation("Embed Button"),
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
      return $image->url();
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

    return $this->dependencies;
  }

}
