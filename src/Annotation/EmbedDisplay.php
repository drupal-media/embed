<?php

/**
 * @file
 * Contains \Drupal\embed\Annotation\EmbedDisplay.
 */

namespace Drupal\embed\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an embed display annotation object.
 *
 * @ingroup embed_api
 *
 * @Annotation
 */
class EmbedDisplay extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the display plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label = '';

  /**
   * The embed type the display can apply to.
   *
   * @var \Drupal\embed\Annotation\EmbedType
   */
  public $embed_types = '';

}
