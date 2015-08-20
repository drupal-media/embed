<?php

namespace Drupal\embed\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Symfony\Component\Routing\Route;

class EmbedButtonEditorAccessCheck implements AccessInterface {

  /**
   * Checks whether or not the embed button is enabled for given editor.
   *
   * Returns allowed if the editor toolbar contains the embed button or neutral
   * otherwise.
   *
   * @code
   * pattern: '/foo/{editor}/{embed_button}'
   * requirements:
   *   _embed_button_filter_access: 'TRUE'
   * @endcode
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $parameters = $route_match->getParameters();
    if ($parameters->has('editor') && $parameters->has('embed_button')) {
      $editor = $parameters->get('editor');
      $embed_button = $parameters->get('embed_button');
      if ($editor instanceof EditorInterface && $embed_button instanceof EmbedButtonInterface) {
        $access = $editor->getFilterFormat()->access('use', $account, TRUE);
        $access = $access->andIf($embed_button->isEnabledInEditor($editor, TRUE));
        return $access;
      }
    }

    // No opinion, so other access checks should decide if access should be
    // allowed or not.
    return AccessResult::neutral();
  }

}
