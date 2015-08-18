<?php

/**
 * @file
 * Contains \Drupal\embed\Form\EmbedButtonForm.
 */

namespace Drupal\embed\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ckeditor\CKEditorPluginManager;
use Drupal\embed\EmbedDisplay\EmbedDisplayManager;
use Drupal\embed\EmbedType\EmbedTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for embed button forms.
 */
class EmbedButtonForm extends EntityForm {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The type plugin manager.
   *
   * @var \Drupal\embed\EmbedType\EmbedTypeManager
   */
  protected $typePluginManager;

  /**
   * The display plugin manager.
   *
   * @var \Drupal\embed\EmbedDisplay\EmbedDisplayManager
   */
  protected $displayPluginManager;

  /**
   * The CKEditor plugin manager.
   *
   * @var \Drupal\ckeditor\CKEditorPluginManager
   */
  protected $ckeditorPluginManager;

  /**
   * The embed settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $entityEmbedConfig;

  /**
   * Constructs a new EmbedButtonForm.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\embed\EmbedType\EmbedTypeManager $embed_type_manager
   *   The embed type plugin manager.
   * @param \Drupal\embed\EmbedDisplay\EmbedDisplayManager $display_plugin_manager
   *   The embed display plugin manager.
   * @param \Drupal\ckeditor\CKEditorPluginManager $ckeditor_plugin_manager
   *   The CKEditor plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityManagerInterface $entity_manager, EmbedTypeManager $embed_type_manager, EmbedDisplayManager $display_plugin_manager, CKEditorPluginManager $ckeditor_plugin_manager, ConfigFactoryInterface $config_factory) {
    $this->entityManager = $entity_manager;
    $this->typePluginManager = $embed_type_manager;
    $this->displayPluginManager = $display_plugin_manager;
    $this->ckeditorPluginManager = $ckeditor_plugin_manager;
    $this->entityEmbedConfig = $config_factory->get('embed.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.embed.type'),
      $container->get('plugin.manager.embed.display'),
      $container->get('plugin.manager.ckeditor.plugin'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    $embed_button = $this->entity;
    $form_state->setTemporaryValue('embed_button', $embed_button);

    $form['label'] = array(
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => $embed_button->label(),
      '#description' => t('The human-readable name of this embed button. This text will be displayed as part of the list on the <em>Add content</em> page. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $embed_button->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$embed_button->isNew(),
      '#machine_name' => array(
        'exists' => ['Drupal\embed\Entity\EmbedButton', 'load'],
      ),
      '#description' => t('A unique machine-readable name for this embed button. It must only contain lowercase letters, numbers, and underscores.'),
    );

    $form['embed_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Embed provider'),
      '#options' => $this->typePluginManager->getDefinitionOptions(),
      '#default_value' => $embed_button->getEmbedType(),
      '#description' => $this->t("Embed type for which this button is to enabled."),
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => '::updateEmbedTypeSettings',
        'effect' => 'fade',
      ),
      '#disabled' => !$embed_button->isNew(),
    );
    if (count($form['embed_type']['#options']) == 0) {
      drupal_set_message($this->t('No embed type providers found.'), 'warning');
    }

    // Add the embed type plugin settings.
    $form['settings'] = array(
      '#type' => 'container',
      '#tree' => TRUE,
      '#prefix' => '<div id="embed-button-settings-wrapper">',
      '#suffix' => '</div>',
    );

    try {
      if ($type_plugin_id = $embed_button->getEmbedType()) {
        $type_plugin = $embed_button->getEmbedTypePlugin();
        $form['settings'][$type_plugin_id] = $type_plugin->buildConfigurationForm(
          array(),
          $form_state
        );
      }
    }
    catch (PluginNotFoundException $exception) {
      drupal_set_message($exception->getMessage(), 'error');
      watchdog_exception('embed', $exception);
      $form['embed_type']['#disabled'] = FALSE;
    }

    $file_scheme = $this->entityEmbedConfig->get('file_scheme');
    $upload_directory = $this->entityEmbedConfig->get('upload_directory');
    $upload_location = $file_scheme . '://' . $upload_directory . '/';

    $form['icon_file'] = array(
      '#title' => $this->t('Button icon image'),
      '#type' => 'managed_file',
      '#description' => $this->t("Image for the button to be shown in CKEditor toolbar. Leave empty to use the default Entity icon."),
      '#upload_location' => $upload_location,
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_image_resolution' => array('32x32', '16x16'),
      ),
    );
    if ($file = $embed_button->getIconFile()) {
      $form['icon_file']['#default_value'] = array('target_id' => $file->id());
    }

    /*$form['display_plugins'] = array(
      '#type' => 'checkboxes',
      '#default_value' => $embed_button->display_plugins ?: array(),
      '#prefix' => '<div id="display-plugins-wrapper">',
      '#suffix' => '</div>',
    );

    $entity_type_id = $form_state->getValue('entity_type') ?: $embed_button->entity_type;
    if ($entity_type_id) {
      $entity_type = $this->entityManager->getDefinition($entity_type_id);
      // If the entity has bundles, allow option to restrict to bundle(s).
      if ($entity_type->hasKey('bundle')) {
        foreach ($this->entityManager->getBundleInfo($entity_type_id) as $bundle_id => $bundle_info) {
          $bundle_options[$bundle_id] = $bundle_info['label'];
        }

        // Hide selection if there's just one option, since that's going to be
        // allowed in either case.
        if (count($bundle_options) > 1) {
          $form['entity_type_bundles'] += array(
            '#title' => $entity_type->getBundleLabel() ?: $this->t('Bundles'),
            '#options' => $bundle_options,
            '#description' => $this->t('If none are selected, all are allowed.'),
          );
        }
      }

      // Allow option to limit display plugins.
      $form['display_plugins'] += array(
        '#title' => $this->t('Allowed display plugins'),
        '#options' => $this->displayPluginManager->getDefinitionOptionsForEntityType($entity_type_id),
        '#description' => $this->t('If none are selected, all are allowed. Note that these are the plugins which are allowed for this entity type, all of these might not be available for the selected entity.'),
      );
    }
    // Set options to an empty array if it hasn't been set so far.
    if (!isset($form['entity_type_bundles']['#options'])) {
      $form['entity_type_bundles']['#options'] = array();
    }
    if (!isset($form['display_plugins']['#options'])) {
      $form['display_plugins']['#options'] = array();
    }*/

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $embed_button = $this->entity;
    if ($embed_button->isNew()) {
      // Get a list of all buttons that are provided by all plugins.
      $all_buttons = array_reduce($this->ckeditorPluginManager->getButtons(), function($result, $item) {
        return array_merge($result, array_keys($item));
      }, array());
      // Ensure that button ID is unique.
      if (in_array($embed_button->id(), $all_buttons)) {
        $form_state->setErrorByName('id', $this->t('A CKEditor button with ID %id already exists.', array('%id' => $embed_button->id())));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    $embed_button = $this->entity;

    // Ensure embed type settings are set correctly.
    // @todo Should this move somewhere else like EmbedButton::preSave()?
    $type = $embed_button->getEmbedType();
    if ($settings = $embed_button->getSettings()) {
      $embed_button->set('settings', array($type => $settings));
    }
    else {
      $embed_button->set('settings', array());
    }

    $icon_fid = $form_state->getValue(array('icon_file', '0'));
    // If a file was uploaded to be used as the icon, get its UUID to be stored
    // in the config entity.
    if (!empty($icon_fid) && $file = $this->entityManager->getStorage('file')->load($icon_fid)) {
      $embed_button->set('icon_uuid', $file->uuid());
    }
    else {
      $embed_button->set('icon_uuid', NULL);
    }

    $status = $embed_button->save();

    $t_args = array('%label' => $embed_button->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The embed button %label has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The embed button %label has been added.', $t_args));
      $context = array_merge($t_args, array('link' => $embed_button->link($this->t('View'), 'collection')));
      $this->logger('embed')->notice('Added embed button %label.', $context);
    }

    $form_state->setRedirectUrl($embed_button->urlInfo('collection'));
  }

  /**
   * {@inheritdoc}
   */
  /*protected function copyFormValuesToEntity(\Drupal\embed\EmbedButtonInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $icon_fid = $form_state->getValue(array('icon_file', '0'));
    // If a file was uploaded to be used as the icon, get its UUID to be stored
    // in the config entity.
    if (!empty($icon_fid) && $file = $this->entityManager->getStorage('file')->load($icon_fid)) {
      $icon_uuid = $file->uuid();
    }
    else {
      $icon_uuid = NULL;
    }

    // Set all form values in the entity except the button icon since it is a
    // managed file element in the form but we want its UUID instead, which
    // will be separately set later.
    foreach ($values as $key => $value) {
      if ($key != 'icon_file') {
        $entity->set($key, $value);
      }
    }

    // Set the UUID of the button icon.
    $entity->set('icon_uuid', $icon_uuid);
  }*/

  /**
   * Ajax callback to update the form fields which depend on entity type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return AjaxResponse
   *   Ajax response with updated options for entity type bundles.
   */
  public function updateEmbedTypeSettings(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for entity type bundles.
    $response->addCommand(new ReplaceCommand(
      '#embed-button-settings-wrapper',
      $form['settings']
    ));

    return $response;
  }

  /**
   * Ajax callback to update the form fields which depend on entity type.
   *
   * @param array $form
   *   The build form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return AjaxResponse
   *   Ajax response with updated options for entity type bundles.
   */
  public function updateEntityTypeDependentFields(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Update options for entity type bundles.
    $response->addCommand(new ReplaceCommand(
      '#bundle-embed-type-wrapper',
      $form['entity_type_bundles']
    ));

    // Update options for display plugins.
    $response->addCommand(new ReplaceCommand(
      '#display-plugins-wrapper',
      $form['display_plugins']
    ));

    return $response;
  }
}
