<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedTypeInterface.
 */

namespace Drupal\embed;

/**
 * Provides an interface for an embed type and its metadata.
 */
interface EmbedTypeInterface {

  /**
   * Gets any arbitrary property.
   *
   * @param string $property
   *   The property to retrieve.
   *
   * @return mixed
   *   The value for that property, or NULL if the property does not exist.
   */
  public function get($property);

  /**
   * Sets a value to an arbitrary property.
   *
   * @param string $property
   *   The property to use for the value.
   * @param mixed $value
   *   The value to set.
   *
   * @return static
   */
  public function set($property, $value);

  /**
   * Return the unique identifier of the embed type.
   *
   * @return string
   *   The unique identifier of the embed type.
   */
  public function id();

  /**
   * Return the human-readable name of the embed type.
   *
   * @return string
   *   The human-readable name of the embed type.
   */
  public function label();

  /**
   * Return the fully qualififed class name of the class which provides the embed form
   *
   * @return string
   *   The name of the embed form class.
   */
  public function getEmbedFormClass();

}
