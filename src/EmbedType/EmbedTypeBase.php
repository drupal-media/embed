<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedType\EmbedTypeBase.
 */

namespace Drupal\embed\EmbedType;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Defines a base display implementation that most display plugins will extend.
 *
 * @ingroup embed_api
 */
abstract class EmbedTypeBase extends PluginBase implements EmbedTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration = array_intersect_key($form_state->getValues(), $this->defaultConfiguration());
    }
  }

  /**
   * Gets a configuration value.
   *
   * @param string $name
   *   The name of the plugin configuration value.
   * @param mixed $default
   *   The default value to return if the configuration value does not exist.
   *
   * @return mixed
   *   The currently set configuration value, or the value of $default if the
   *   configuration value is not set.
   */
  public function getConfigurationValue($name, $default = NULL) {
    $configuration = $this->getConfiguration();
    return array_key_exists($name, $configuration) ? $configuration[$name] : $default;
  }
}
