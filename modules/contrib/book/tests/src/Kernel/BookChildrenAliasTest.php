<?php

declare(strict_types=1);

namespace Drupal\Tests\book\Kernel;

use Drupal\book\BookManager;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\pathauto\Entity\PathautoPattern;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests cascading pathauto alias updates for book descendants.
 */
#[Group('book')]
#[CoversClass(BookManager::class)]
#[RunTestsInSeparateProcesses]
class BookChildrenAliasTest extends KernelTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'book',
    'path',
    'path_alias',
    'pathauto',
    'token',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('path_alias');
    $this->installSchema('book', ['book']);
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['node', 'book', 'pathauto', 'system']);

    $this->createContentType(['type' => 'page']);
    $this->config('book.settings')
      ->set('allowed_types', [
        ['content_type' => 'page', 'child_type' => 'page'],
      ])
      ->set('update_child_aliases', TRUE)
      ->save();

    PathautoPattern::create([
      'id' => 'node_pattern',
      'type' => 'canonical_entities:node',
      'pattern' => '/content/[node:title]',
      'weight' => 10,
    ])->save();
  }

  /**
   * Tests that descendant aliases are regenerated after parent title change.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function testDescendantAliasesUpdatedWhenEnabled(): void {
    $root = Node::create([
      'type' => 'page',
      'title' => 'Parent',
      'book' => ['bid' => 'new'],
    ]);
    $root->save();

    $child = Node::create([
      'type' => 'page',
      'title' => 'Child',
      'book' => [
        'bid' => $root->id(),
        'pid' => $root->id(),
      ],
    ]);
    $child->save();

    // Verify the child's initial alias.
    $alias_manager = $this->container->get('path_alias.manager');
    $this->assertSame('/content/child', $alias_manager->getAliasByPath('/node/' . $child->id()));

    // Rename the parent and save.
    $root->setTitle('Renamed Parent');
    $root->save();

    // Reload the child and verify the alias was regenerated.
    $alias_manager->cacheClear();
    $child = Node::load($child->id());
    $child_alias = $alias_manager->getAliasByPath('/node/' . $child->id());
    $this->assertSame('/content/child', $child_alias);
  }

}
