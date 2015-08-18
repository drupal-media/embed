<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedTypeInterface.
 */

namespace Drupal\embed\EmbedType;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for an embed type and its metadata.
 *
 * @ingroup embed_api
 */
interface EmbedTypeInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

}
