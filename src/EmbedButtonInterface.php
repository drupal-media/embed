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
   * Returns the embed type for which this button is enabled.
   *
   * @return string
   *   Machine name of the embed type.
   */
  public function getEmbedType();

  /**
   * Returns the label of the embed type for which this button is enabled.
   *
   * @return string
   *   Human readable label of the embed type.
   */
  public function getEmbedTypeLabel();

  /**
   * Returns the button's icon file.
   *
   * @return \Drupal\file\FileInterface
   *   The file entity of the button icon.
   */
  public function getIconFile();

  /**
   * Returns the URL of the button's icon.
   *
   * @return string
   *   The URL of the button icon.
   */
  public function getIconUrl();

  /**
   * Returns the list of display plugins allowed for the embed type.
   *
   * @return array
   *   List of allowed display plugins.
   */
  public function getAllowedDisplayPlugins();

}
