<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedTypeManager.
 */

namespace Drupal\embed;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Embed type plugin manager.
 *
 * @see \Drupal\embed\Annotation\EmbedType
 * @see \Drupal\embed\EmbedTypeInterface
 */
class EmbedTypeManager extends DefaultPluginManager {

  /**
   * Constructs a EmbedTypeManager object.
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
    parent::__construct('Plugin/EmbedType', $namespaces, $module_handler, 'Drupal\embed\EmbedTypeInterface', 'Drupal\embed\Annotation\EmbedType');
    $this->alterInfo('embed_type_plugin_info');
    $this->setCacheBackend($cache_backend, 'embed_type_plugins');
  }

  /**
   * Determines the Plugins that satisfy a particular category.
   *
   * @param string $category
   *   A string of type category.
   *
   * @return array
   *   An array of plugin definitions.
   */
  public function getDefinitionForCategory($category){
    $definitions = $this->getDefinitions();
    for($i = 0; $i < count($definitions); $i++){
      if($definitions[$i]->category == $category){
        array_push($definitions_for_category,$definitions[$i]);
      }
    }
    return $definitions_for_category;
  }
}
