<?php

/**
 * @file
 * Contains \Drupal\embed\EmbedButtonListBuilder.
 */

namespace Drupal\embed;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of EmbedButton.
 */
class EmbedButtonListBuilder extends ConfigEntityListBuilder {

  /**
    * {@inheritdoc}
    */
  public function buildHeader() {
      $header['label'] = $this->t('Embed button');
      $header['button_label'] = $this->t('Button Label');
      return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['button_label'] = $entity->getButtonLabel();
    return $row + parent::buildRow($entity);
  }

}
