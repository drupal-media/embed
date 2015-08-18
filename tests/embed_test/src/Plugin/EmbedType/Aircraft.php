<?php

/**
 * @file
 * Contains \Drupal\embed_test\Plugin\EmbedType\Aircraft.
 */

namespace Drupal\embed_test\Plugin\EmbedType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\embed\EmbedType\EmbedTypeBase;

/**
 * Aircraft test embed type.
 *
 * @EmbedType(
 *   id = "aircraft",
 *   label = @Translation("Aircraft")
 * )
 */
class Aircraft extends EmbedTypeBase {

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
      '#title' => $this->t('Type'),
      '#options' => array(
        'aerostats' => $this->t('Lighter than air (aerostats)'),
        'fixed-wing' => $this->t('Fixed-wing'),
        'rotorcraft' => $this->t('Rotorcraft'),
      ),
      '#default_value' => $this->getConfigurationValue('aircraft_type'),
      '#required' => TRUE,
    );

    return $form;
  }

}
