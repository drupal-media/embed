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
    $header['embed_type'] = $this->t('Embed type');
    $header['icon'] = [
      'data' => $this->t('Icon'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\embed\EmbedButtonInterface $entity */
    $row['label'] = $this->getLabel($entity);
    $row['embed_type'] = $entity->getEmbedTypeLabel();
    $row['icon'] = \Drupal::theme()->render('image', [
      'uri' => $entity->getIconUrl(),
      'alt' => $this->t('Button icon for the @label button', array('@label' => $this->getLabel($entity)))
    ]);
    return $row + parent::buildRow($entity);
  }

}
