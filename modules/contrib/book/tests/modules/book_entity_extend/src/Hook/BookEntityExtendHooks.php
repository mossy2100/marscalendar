<?php

declare(strict_types=1);

namespace Drupal\book_entity_extend\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\book_entity_extend\Entity\Bundle\CommonNode;

/**
 * Hook implementations for book_entity_extend.
 */
class BookEntityExtendHooks {

  /**
   * Implements hook_entity_bundle_info_alter().
   */
  #[Hook('entity_bundle_info_alter')]
  public function entityBundleInfoAlter(array &$bundles): void {
    if (isset($bundles['node']['article'])) {
      $bundles['node']['article']['class'] = CommonNode::class;
    }
  }

}
