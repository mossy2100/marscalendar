<?php

namespace Drupal\rdf_test_namespaces\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for rdf_test_namespaces.
 */
class RdfTestNamespacesHooks {
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
      'foaf' => 'http://xmlns.com/foaf/0.1/',
      'foaf1' => 'http://xmlns.com/foaf/0.1/',
    ];
  }

}
