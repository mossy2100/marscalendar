<?php

namespace Drupal\rdf_conflicting_namespaces\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for rdf_conflicting_namespaces.
 */
class RdfConflictingNamespacesHooks {
  /**
   * @file
   * Test the namespace registration functionality.
   */

  /**
   * Implements hook_rdf_namespaces().
   */
  #[Hook('rdf_namespaces')]
  public static function rdfNamespaces() {
    return [
      'dc' => 'http://purl.org/conflicting/namespace',
    ];
  }

}
