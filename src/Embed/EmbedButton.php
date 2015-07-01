<?php

/**
 * @file
 * Contains \Drupal\embed\Embed\EmbedButton.
 */

namespace Drupal\embed\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\embed\EmbedButtonInterface;
use Drupal\embed\EntityHelperTrait;
/**
 * Defines the EmbedButton entity.
 *
 * @ConfigEntityType(
 *   id = "embed_button",
 *   label = @Translation("Embed Button"),
 *   handlers = {
 *      "list_builder" = "Drupal\embed\EmbedButtonListBuilder",
 *      "form" = {
 *        "add" = "Drupal\embed\Form\EmbedButtonForm",
 *        "edit" = "Drupal\embed\Form\EmbedButtonForm",
 *        "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *       }
 *   },
 *   config_prefix = "embed_button",
 *   admin_permission = "administer embed buttons",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *      "label",
 *      "id",
 *      "button_label",
 *      "button_icon_uuid"
 *   },
 *   links = {
 *      "edit-form" = "/admin/config/content/embed-button/{embed_button}",
 *      "delete-form" = "/admin/config/content/embed-button/{embed_button}/delete"
 *   }
 * )
 */
class EmbedButton extends ConfigEntityBase implements EmbedButtonInterface {
  use EntityHelperTrait;

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
   *             */
  public $label;

  /**
   * Label of the button shown in CKEditor toolbar.
   *
   * @var string
   */
  public $button_label;

  /**
   * UUID of the button's icon file.
   *
   * @var string
   */
  public $button_icon_uuid;

  /**
   * {@inheritdoc}
   */
  public function getButtonLabel() {
    return $this->button_label;
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
     if ($this->button_icon_uuid && $file_icon = $this->entityManager()->loadEntityByUuid('file', $this->button_icon_uuid)) {
       $this->addDependency($file_icon->getConfigDependencyKey(), $file_icon->getConfigDependencyName());
     }
     return $this->dependencies;
   }
}
