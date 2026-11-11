<?php

declare(strict_types=1);

namespace Drupal\Tests\book\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests Book with a custom bundle class.
 */
#[Group('book')]
class BookCustomBundleClassTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use UserCreationTrait;

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
    'book_entity_extend',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('book', ['book']);
    $this->installConfig(['system', 'node', 'book']);

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
   * Viewing a node whose bundle class lacks getBook() must not fatal.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function testViewingNodeWithoutBookInterfaceDoesNotFatal(): void {
    $display = $this->container->get('entity_display.repository')->getViewDisplay('node', 'article');
    $display->setComponent('book_navigation', ['weight' => 100])->save();

    $node = Node::create(['type' => 'article', 'title' => 'Test page']);
    $node->save();

    $build = $this->container->get('entity_type.manager')
      ->getViewBuilder('node')
      ->view($node);
    $this->container->get('renderer')->renderRoot($build);

    $this->addToAssertionCount(1);
  }

  /**
   * Building the edit form of such a node must not fatal.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testEditFormWithoutBookInterfaceDoesNotFatal(): void {
    $this->setUpCurrentUser([], ['add content to books', 'administer book outlines'], TRUE);

    $node = Node::create(['type' => 'article', 'title' => 'Test page']);
    $node->save();

    $form = $this->container->get('entity.form_builder')->getForm($node, 'edit');

    $this->assertArrayNotHasKey('book', $form);
  }

  /**
   * Checking outline removal access for such a node must not fatal.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRemoveAccessWithoutBookInterfaceDoesNotFatal(): void {
    $this->setUpCurrentUser([], ['add content to books', 'administer book outlines'], TRUE);

    $node = Node::create(['type' => 'article', 'title' => 'Test page']);
    $node->save();

    $url = $node->toUrl('book-remove-form');

    $this->assertFalse($url->access());
  }

}
