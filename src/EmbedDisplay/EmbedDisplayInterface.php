<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedDisplay\EntityEmbedDisplayInterface.
 */

namespace Drupal\embed\EmbedDisplay;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the required interface for all embed display plugins.
 *
 * @ingroup embed_api
 */
interface EmbedDisplayInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface, AccessibleInterface {

  /**
   * Builds and returns the renderable array for this display plugin.
   *
   * @return array
   *   A renderable array representing the content of the embedded item.
   */
  public function build();

}
