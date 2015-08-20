<?php

/**
 * @file
 * Contains \Drupal\embed_test\Plugin\EmbedType\EmbedTestValidation.
 */

namespace Drupal\embed_test\Plugin\EmbedType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\embed\EmbedType\EmbedTypeBase;

/**
 * Aircraft test embed type.
 *
 * @EmbedType(
 *   id = "embed_test_validation",
 *   label = @Translation("Aircraft")
 * )
 */
class EmbedTestValidation extends EmbedTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'aircraft_type' => 'fixed-wing',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['aircraft_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Aircraft type'),
      '#options' => array(
        'aerostats' => $this->t('Lighter than air (aerostats)'),
        'fixed-wing' => $this->t('Fixed-wing'),
        'rotorcraft' => $this->t('Rotorcraft'),
        'helicopters' => $this->t('Helicopers'),
        'invalid' => $this->t('Invalid type'),
      ),
      '#default_value' => $this->getConfigurationValue('aircraft_type'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('aircraft_type') === 'invalid') {
      $form_state->setError($form['aircraft_type'], $this->t('Invalid aircraft type.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('aircraft_type') === 'helicopters') {
      drupal_set_message($this->t('Helicopters are just rotorcraft.'), 'warning');
      $form_state->setValue('aircraft_type', 'rotocraft');
    }

    parent::submitConfigurationForm($form, $form_state);
  }

}
