<?php

declare(strict_types=1);

namespace Drupal\Tests\book\Kernel;

use Drupal\book\Entity\Node\Book;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests bundle class extension for book module.
 */
#[Group('book')]
class BookBundleClassTest extends KernelTestBase {

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
    'book_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('book', ['book']);
    $this->installConfig(['node', 'book']);

    $this->createContentType(['type' => 'page']);
    $this->createContentType(['type' => 'article']);

    $book_config = $this->config('book.settings');
    $allowed_types = $book_config->get('allowed_types') ?? [];
    $allowed_types[] = [
      'content_type' => 'article',
      'child_type' => 'article',
    ];
    $book_config->set('allowed_types', $allowed_types)->save();
  }

  /**
   * Tests that bundle class works for allowed vs non-allowed bundles.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBundleClassAssignment(): void {
    $page = Node::create([
      'type' => 'page',
      'title' => 'Test page',
    ]);
    $page->save();

    $article = Node::create([
      'type' => 'article',
      'title' => 'Test article',
    ]);
    $article->save();

    // Verify the node bundle class is being used since it's not an
    // allowed book.
    $this->assertInstanceOf(Node::class, $page);
    // Verify we can load the node.
    $loaded_node = Node::load($page->id());
    $this->assertEquals('Test page', $loaded_node->getTitle());

    $this->assertInstanceOf(Book::class, $article);
    // Verify we can load the node.
    $loaded_node = Node::load($article->id());
    $this->assertEquals('Test article', $loaded_node->getTitle());
  }

}
