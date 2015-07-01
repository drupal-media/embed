<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EmbedButtonInterface.
 */

namespace Drupal\embed;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a embed button entity.
 */

interface EmbedButtonInterface extends ConfigEntityInterface {

  /**
    * Returns the label for the button to be shown in CKEditor toolbar.
    *
    * @return string
    *   Label for the button.
    */
  public function getButtonLabel();

  /**
   * Returns the list of bundles selected for the entity type.
   *
   * @return array
   *   List of allowed bundles.
   */
  public function getEntityTypeBundles();

  /**
   * Returns the list of bundles selected for the entity type.
   *
   * @return array
   *   List of allowed bundles.
   */
  public function getEntityTypeBundles();
}
