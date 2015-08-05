<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedDisplay\EntityEmbedDisplayManager.
 */

namespace Drupal\embed\EmbedDisplay;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Embed display plugin manager.
 *
 * @see \Drupal\embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\embed\EmbedDisplay\EmbedDisplayInterface
 *
 * @todo Convert this to use ContextAwarePluginManagerTrait
 * @see https://drupal.org/node/2277981
 */
class EmbedDisplayManager extends DefaultPluginManager {

  /**
   * Constructs a new class instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EmbedDisplay', $namespaces, $module_handler, 'Drupal\embed\EmbedDisplay\EmbedDisplayInterface', 'Drupal\embed\Annotation\EmbedDisplay');
    $this->alterInfo('embed_display_plugin_info');
    $this->setCacheBackend($cache_backend, 'embed_display_plugins');
  }

  /**
   * Provides a list of plugins that can be used for a certain embed type.
   *
   * @param string $embed_type
   *   The embed type id.
   *
   * @return array
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForEmbedType($embed_type) {
    $definitions = $this->getDefinitions();
    $valid_definitions = array_filter($definitions, function ($definition) use ($embed_type) {
      return $definition['embed_type'] === $embed_type;
    });
    return array_map(function ($definition) {
      return (string) $definition['label'];
    }, $valid_definitions);
  }

}
