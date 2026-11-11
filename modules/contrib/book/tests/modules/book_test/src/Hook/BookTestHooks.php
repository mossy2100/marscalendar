<?php

namespace Drupal\book_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for book_test.
 */
class BookTestHooks {
  /**
   * @file
   * Test module for testing the book module.
   *
   * This module's functionality depends on the following state variables:
   * - book_test.debug_book_navigation_cache_context: Used in NodeQueryAlterTest
   *   to enable the node_access_all grant realm.
   *
   * @see \Drupal\Tests\book\Functional\BookTest::testBookNavigationCacheContext()
   */

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public static function pageAttachments(array &$page): void {
    $page['#cache']['tags'][] = 'book_test.debug_book_navigation_cache_context';
    if (\Drupal::state()->get('book_test.debug_book_navigation_cache_context', FALSE)) {
      \Drupal::messenger()->addStatus(\Drupal::service('cache_contexts_manager')->convertTokensToKeys([
        'route.book_navigation',
      ])->getKeys()[0]);
    }
  }

  /**
   * Implements hook_entity_type_build().
   *
   * @see \Drupal\Tests\book\Functional\BookTest::testBookWithFieldViolations()
   */
  #[Hook('entity_type_build')]
  public static function entityTypeBuild(array &$entity_types): void {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */
    $entity_types['node']->addConstraint('BookTestTitle', []);
  }

}
