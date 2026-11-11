<?php

namespace Drupal\Tests\book\Kernel;

use Drupal\book\BookManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\node\Entity\Node;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that bookSubtreeData() respects node access per user.
 */
#[Group('book')]
#[RunTestsInSeparateProcesses]
class BookSubtreeAccessTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'text',
    'node',
    'book',
    'book_content_type',
  ];

  /**
   * The book manager service.
   */
  protected BookManagerInterface $bookManager;

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installSchema('book', ['book']);
    $this->installConfig(['node', 'book', 'book_content_type', 'field']);

    $config = \Drupal::configFactory()->getEditable('book.settings');
    $config->set('allowed_types', [
      ['content_type' => 'book', 'child_type' => 'book'],
    ])->save();

    $this->bookManager = $this->container->get('book.manager');
  }

  /**
   * Tests that bookSubtreeData() does not leak unpublished child nodes.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function testSubtreeAccessFilteringPerUser(): void {
    // Create an admin user who can view unpublished content.
    $admin = $this->createUser([
      'administer book outlines',
      'access content',
      'bypass node access',
    ]);

    // Create a low-privileged user who can only view published content.
    $authenticated = $this->createUser([
      'access content',
    ]);

    // Set up as admin to create book structure.
    $this->container->get('current_user')->setAccount($admin);

    // Create book: Root (published) -> Child (unpublished).
    $root = Node::create([
      'type' => 'book',
      'title' => 'Book Root',
      'status' => 1,
      'book' => ['bid' => 'new'],
    ]);
    $root->save();

    $published_child = Node::create([
      'type' => 'book',
      'title' => 'Published Child',
      'status' => 1,
      'book' => ['bid' => $root->id(), 'pid' => $root->id()],
    ]);
    $published_child->save();

    $unpublished_child = Node::create([
      'type' => 'book',
      'title' => 'Unpublished Child',
      'status' => 0,
      'book' => ['bid' => $root->id(), 'pid' => $root->id()],
    ]);
    $unpublished_child->save();

    // Step 1: Admin requests subtree — this primes the cache.
    $root_link = $this->bookManager->loadBookLink($root->id(), FALSE);
    $admin_tree = $this->bookManager->bookSubtreeData($root_link);

    // Admin should see all 3 nodes (root + 2 children).
    $admin_nids = $this->collectNidsFromTree($admin_tree);
    $this->assertContains($root->id(), $admin_nids);
    $this->assertContains($published_child->id(), $admin_nids);
    $this->assertContains($unpublished_child->id(), $admin_nids);

    // Step 2: Switch to low-privileged user and request the same subtree.
    // The cache is now primed from the admin request.
    $this->container->get('current_user')->setAccount($authenticated);

    // Re-load the link to avoid stale data.
    $root_link = $this->bookManager->loadBookLink($root->id(), FALSE);
    $user_tree = $this->bookManager->bookSubtreeData($root_link);

    // Low-privileged user should NOT see the unpublished child.
    $user_nids = $this->collectNidsFromTree($user_tree);
    $this->assertContains($root->id(), $user_nids);
    $this->assertContains($published_child->id(), $user_nids);
    $this->assertNotContains($unpublished_child->id(), $user_nids);
  }

  /**
   * Recursively collects all node IDs from a book tree.
   *
   * @param array $tree
   *   A book tree array as returned by bookSubtreeData().
   *
   * @return int[]
   *   An array of node ID's found in the tree.
   */
  protected function collectNidsFromTree(array $tree): array {
    $nids = [];
    foreach ($tree as $item) {
      $nids[] = $item['link']['nid'];
      if (!empty($item['below'])) {
        $nids = array_merge($nids, $this->collectNidsFromTree($item['below']));
      }
    }
    return $nids;
  }

}
