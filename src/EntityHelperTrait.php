<?php

/**
  * @file
  * Contains Drupal\embed\EntityHelperTrait.
 */

namespace Drupal\embed;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\embed\EmbedDisplay\EneEmbedDisplayManager;

/**
  * Wrapper methods for entity loading and rendering.
  *
  * This utility trait should only be used in application-level code, such as
  * classes that would implement ContainerInjectionInterface. Services registered
  * in the Container should not use this trait but inject the appropriate service
  * directly for easier testing.
 */
trait EntityHelperTrait{

  /**
    * The entity manager service.
    *
    *  @var \Drupal\Core\Entity\EntityManagerInterface
    */
   protected $entityManager;

  /**
    * The module handler service.
    *
    * @var \Drupal\Core\Extension\ModuleHandlerInterface.
    */
   protected $moduleHandler;

  /**
    * The display plugin manager.
    *
    * @var \Drupal\embed\EmbedDisplay\EmbedDisplayManager.
    */
   protected $displayPluginManager;

   /**
     * Returns the render array for an entity.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to be rendered.
     * @param string $view_mode
     *   The view mode that should be used to display the entity.
     * @param string $langcode
     *   (optional) For which language the entity should be rendered, defaults to
     *   the current content language.
     *
     * @return array
     *   A render array for the entity.
    */
   protected function renderEntity(EntityInterface $entity, $view_mode, $langcode = NULL) {
     $render_controller = $this->entityManager()->getViewBuilder($entity->getEntityTypeId());
     return $render_controller->view($entity, $view_mode, $langcode);
   }

   /**
     * Renders an entity using an EmbedDisplay plugin.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to be rendered.
     * @param string $plugin_id
     *   The EmbedDisplay plugin ID.
     * @param array $plugin_configuration
     *   (optional) Array of plugin configuration values.
     * @param array $context
     *   (optional) Array of additional context values, usually the embed HTML
     *   tag's attributes.
     *
     * @return string
     *   The HTML of the entity rendered with the display plugin.
     *
     * @throws \Drupal\embed\RecursiveRenderingException;
     *   Throws an exception when the post_render_cache callback goes into a
     *   potentially infinite loop.
     */
   protected function renderEmbedDisplayPlugin(EntityInterface $entity, $plugin_id, array $plugin_configuration = array(), array $context = array()) {
     // Protect ourselves from recursive rendering.
     static $depth = 0;
     $depth++;
     if ($depth > 20) {
             throw new RecursiveRenderingException(SafeMarkup::format('Recursive rendering detected when rendering entity @entity_type(@entity_id). Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id())));
     }

     // Allow modules to alter the entity prior to display rendering.
     $this->moduleHandler()->invokeAll('entity_preembed', array($entity, $context));

     // Build the display plugin.
     $display = $this->displayPluginManager()->createInstance($plugin_id, $plugin_configuration);
     $display->setContextValue('entity', $entity);
     $display->setAttributes($context);

     // Check if the display plugin is accessible. This also checks entity
     // access, which is why we never call $entity->access() here.
     if (!$display->access()) {
       return '';
     }

     // Build and render the display plugin, allowing modules to alter the
     // result before rendering.
     $build = $display->build();
     $this->moduleHandler()->alter('embed', $build, $display);
     $entity_output = drupal_render($build);

     $depth--;
     return $entity_output;
   }

   /**
     * Check access to an entity.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity object.
     * @param string $op
     *   (optional) The operation to be performed. Defaults to view.
     * @param \Drupal\Core\Session\AccountInterface $account
     *   (optional) The user for which to check access, or NULL to check access
     *  for the current user. Defaults to NULL.
     *
     * @return bool|null
     *   self::ALLOW, self::DENY, or self::KILL.
     */
   protected function accessEntity(EntityInterface $entity, $op = 'view', AccountInterface $account = NULL) {
     switch ($entity->getEntityTypeId()) {
       case 'file':
         // Due to issues with access checking with file entities in core,
         // we cannot actually use Entity::access() which would have been called
         // by parent::access().
         //
         // @see https://drupal.org/node/2128791
         // @see https://drupal.org/node/2148353
         // @see https://drupal.org/node/2078473
         $uri = $entity->getFileUri();
         switch (file_uri_scheme($uri)) {
           case 'public':
             return TRUE;
           case 'private':
           case 'temporary':
             $headers = $this->moduleHandler()->invokeAll('file_download', array($uri));
             foreach ($headers as $result) {
               if ($result == -1) {
                 return FALSE;
               }
             }

             if (count($headers)) {
               return TRUE;
             }
             break;
         }
         default:
           return $entity->access($op, $account);
     }
   }

   /**
     * Returns the entity manager.
     *
     * @return \Drupal\Core\Entity\EntityManagerInterface
     *   The entity manager.
     */
   protected function entityManager() {
      if (!isset($this->entityManager)) {
        $this->entityManager = \Drupal::entityManager();
      }
      return $this->entityManager;
   }

   /**
     * Sets the entity manager service.
     *
     * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
     *   The entity manager service.
     *
     * @return self
     */
   public function setEntityManager(EntityManagerInterface $entity_manager) {
     $this->entityManager = $entity_manager;
     return $this;
   }


   /**
     * Returns the module handler.
     *
     * @return \Drupal\Core\Extension\ModuleHandlerInterface
     *   The module handler.
     */
   protected function moduleHandler() {
     if (!isset($this->moduleHandler)) {
       $this->moduleHandler = \Drupal::moduleHandler();
     }
     return $this->moduleHandler;
   }

   /**
     * Sets the module handler service.
     *
     * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
     *   The module handler service.
     *
     * @return self
     */
   public function setModuleHandler(ModuleHandlerInterface $module_handler) {
     $this->moduleHandler = $module_handler;
     return $this;
   }

   /**
     * Returns the display plugin manager.
     *
     * @return \Drupal\embed\EmbedDisplay\EmbedDisplayManager
     *   The display plugin manager.
     */
   protected function displayPluginManager() {
     if (!isset($this->displayPluginManager)) {
       $this->displayPluginManager = \Drupal::service('plugin.manager.embed.display');
     }
     return $this->displayPluginManager;
   }

   /**
     * Sets the display plugin manager service.
     *
     * @param \Drupal\embed\EmbedDisplay\EmbedDisplayManager $display_plugin_manager
     *   The display plugin manager service.
     *
     * @return self
     */
   public function setDisplayPluginManager(EmbedDisplayManager $display_plugin_manager) {
     $this->displayPluginManager = $display_plugin_manager;
     return $this;
   }
}
