<?php /**
 * @file
 * Contains \Drupal\permissions_by_term\Controller\DefaultController.
 */

namespace Drupal\permissions_by_term\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Component\Utility\Tags;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\Core\Entity\Entity;
use \Drupal\permissions_by_term\AccessCheckService;
use \Drupal\Component\Utility\SafeMarkup;
use \Drupal\Core\Access\AccessResult;

/**
 * Default controller for the permissions_by_term module.
 */
class PermissionsByTermController extends ControllerBase {

  public function __construct()
  {
    $this->oAccessCheckService = new AccessCheckService();
  }

  /**
   * Handles views in module's logic.
   *
   * @param $view
   */
  public function handleViews(&$view) {

    if ($this->oAccessCheckService->viewContainsNode($view) === TRUE) {
      $this->oAccessCheckService->removeForbiddenNodesFromView($view);
    }

  }

  /**
   * Handles nodes in module's logic.
   *
   * @param $iNid
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function handleNode($iNid) {
    if ($this->oAccessCheckService->canUserAccessByNodeId($iNid) === TRUE) {
      return AccessResult::allowed();
    } else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Returns JSON response for user's autocomplete field in permissions form.
   *
   * @return JsonResponse
   */
  public function permissions_by_term_autocomplete_multiple() {
    // The user enters a comma-separated list of users.
    // We only autocomplete the last user.
    $array = Tags::explode($_REQUEST['q']);

    // Fetch last user.
    $last_string = trim(array_pop($array));

    $matches = [];

    $aUserIds = \Drupal::entityQuery('user')
      ->condition('name', $last_string, 'CONTAINS')
      ->execute();

    $prefix = count($array) ? implode(', ', $array) . ', ' : '';

    foreach ($aUserIds as $iUserId) {
      $oUser = user_load($iUserId);
      $matches[$prefix . $oUser->getUsername()] = SafeMarkup::checkPlain($oUser->getUsername());
    }

    return new JsonResponse($matches);
  }

}