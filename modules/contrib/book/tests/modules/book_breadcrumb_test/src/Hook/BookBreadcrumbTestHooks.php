<?php

namespace Drupal\book_breadcrumb_test\Hook;

use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for book_breadcrumb_test.
 */
class BookBreadcrumbTestHooks {

  /**
   * Implements hook_ENTITY_TYPE_access().
   */
  #[Hook('node_access')]
  public static function nodeAccess(NodeInterface $node, $operation, AccountInterface $account): AccessResultInterface {
    $config = \Drupal::config('book_breadcrumb_test.settings');
    if ($config->get('hide') && $node->getTitle() == "you can't see me" && $operation == 'view') {
      $access = new AccessResultForbidden();
    }
    else {
      $access = new AccessResultNeutral();
    }
    $access->addCacheableDependency($config);
    $access->addCacheableDependency($node);
    return $access;
  }

}
