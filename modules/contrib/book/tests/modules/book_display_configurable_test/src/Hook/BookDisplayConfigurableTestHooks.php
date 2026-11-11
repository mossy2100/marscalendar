<?php

namespace Drupal\book_display_configurable_test\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for book_display_configurable_test.
 */
class BookDisplayConfigurableTestHooks {

  /**
   * Implements hook_entity_base_field_info_alter().
   */
  #[Hook('entity_base_field_info_alter')]
  public static function entityBaseFieldInfoAlter($base_field_definitions, EntityTypeInterface $entity_type): void {
    if ($entity_type->id() == 'node') {
      foreach ([
        'created',
        'uid',
        'title',
      ] as $field) {
        /** @var \Drupal\Core\Field\BaseFieldDefinition[] $base_field_definitions */
        $base_field_definitions[$field]->setDisplayConfigurable('view', TRUE);
      }
    }
  }

  /**
   * Implements hook_entity_type_build().
   */
  #[Hook('entity_type_build')]
  public static function entityTypeBuild(array $entity_types): void {
    // Allow skipping of extra preprocessing for configurable display.
    $entity_types['node']->set('enable_base_field_custom_preprocess_skipping', TRUE);
    $entity_types['node']->set('enable_page_title_template', TRUE);
  }

}
