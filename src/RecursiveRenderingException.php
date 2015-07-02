<?php

/**
 * @file
 * Contains \Drupal\embed\RecursiveRenderingException.
 */

namespace Drupal\embed;

/**
 * Exception thrown when the embed entity post_render_cache callback goes into
 * a potentially infinite loop.
 */
class RecursiveRenderingException extends \Exception {}
