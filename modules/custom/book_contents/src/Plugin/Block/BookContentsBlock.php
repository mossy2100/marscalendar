<?php

namespace Drupal\book_contents\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Book contents' block.
 *
 * @Block(
 *   id = "book_contents",
 *   admin_label = @Translation("Book contents"),
 *   category = @Translation("Menus")
 * )
 */
class BookContentsBlock extends BlockBase {

  /**
   * The book's pages.
   *
   * @var array
   */
  protected $pages;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_mode' => "all pages",
    ];
  }

  /**
   * {@inheritdoc}
   */
  function blockForm($form, FormStateInterface $form_state) {
    $options = [
      'all pages'  => $this->t('Show block on all pages'),
      'book pages' => $this->t('Show block only on book pages'),
    ];
    $form['book_block_mode'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Book contents block display'),
      '#options'       => $options,
      '#default_value' => $this->configuration['block_mode'],
      '#description'   => $this->t("If <em>Show block on all pages</em> is selected, the block will contain the automatically generated menus for all of the site's books. If <em>Show block only on book pages</em> is selected, the block will contain only the one menu corresponding to the current page's book. In this case, if the current page is not in a book, no block will be displayed. The <em>Page specific visibility settings</em> or other visibility settings can be used in addition to selectively display this block."),
    ];

    $conn = Database::getConnection();
    $books = $conn->query("select b.bid, n.title
      from book b
      left join node_field_data n on b.nid = n.nid
      where b.pid = 0
    ");
    $book_options = [];
    foreach ($books as $book) {
      $book_options[$book->bid] = $book->title;
    }
    $form['bid'] = [
      '#type'        => 'select',
      '#title'       => $this->t('Book to show contents of'),
      '#options'     => $book_options,
      '#description' => $this->t("Select which book to show the contents of in the block."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_mode'] = $form_state->getValue('book_block_mode');
    $this->configuration['bid'] = $form_state->getValue('bid');
  }

  /**
   * Get the HTML for a book contents list item.
   * Recursive function.
   *
   * @param int $nid
   *   The nid ID of the page.
   *
   * @return string
   */
  protected function itemHtml($nid) {
    // Get the page object.
    $page = $this->pages[$nid];

    // Ignore unpublished pages.
    if (!$page->status) {
      return '';
    }

    // Get the current nid.
    $node = \Drupal::routeMatch()->getParameter('node');
    $current_nid = (bool) $node ? $node->id() : 0;

    $html = '';
    $bid = $this->configuration['bid'];

    // Get alias.
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath("/node/$nid");

    // Render link.
    $html .= "<a href='$alias' class='";
    if ($nid == $bid) {
      $html .= " top-page";
    }
    if ($nid == $current_nid) {
      $html .= " current-page";
    }
    $html .= "'>" . $page->title . "</a>\n";

    // Render children.
    $list_started = FALSE;
    foreach ($this->pages as $page2) {
      if ($page2->status && $page2->pid == $nid) {
        if (!$list_started) {
          $html .= "<ul>\n";
          $list_started = TRUE;
        }
        $html .= "<li>" . $this->itemHtml($page2->nid) . "</li>\n";
      }
    }
    if ($list_started) {
      $html .= "</ul>\n";
    }

    return $html;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the pages.
    $bid = $this->configuration['bid'];
    if (!$bid) {
      $html = "No book selected. Configure the block, dude.";
    }
    else {
      $this->pages = [];
      $conn = Database::getConnection();
      $rs = $conn->query("
        select b.nid, b.pid, n.title, n.status
        from book b
        left join node_field_data n on b.nid = n.nid
        where bid = :bid
        order by pid, weight
      ", [':bid' => $bid]);
      foreach ($rs as $rec) {
        $this->pages[$rec->nid] = $rec;
      }
      $html = $this->itemHtml($bid);
    }

    // Build the HTML.
    return [
      '#markup' => $html,
      '#cache'  => [
        'disabled' => TRUE,
      ],
    ];
  }
}
