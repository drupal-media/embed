<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedButtonInterface.
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

}
