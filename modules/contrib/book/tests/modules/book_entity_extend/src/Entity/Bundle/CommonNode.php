<?php

declare(strict_types=1);

namespace Drupal\book_entity_extend\Entity\Bundle;

use Drupal\node\Entity\Node;

/**
 * Bundle class that intentionally does NOT implement BookInterface.
 *
 * Mirrors a common contrib pattern where a site puts a custom bundle class on
 * a node type.
 */
class CommonNode extends Node {
}
