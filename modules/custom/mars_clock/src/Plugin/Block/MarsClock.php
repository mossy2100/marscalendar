<?php
/**
 * Created by PhpStorm.
 * User: shaun
 * Date: 26/4/17
 * Time: 18:34
 */

namespace Drupal\mars_clock\Plugin\Block;

use Drupal\Core\block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Mars Clock' block showing the Utopian Date and Time.
 *
 * @Block(
 *   id = "mars_clock",
 *   admin_label = @Translation("Mars clock"),
 * )
 */

class MarsClock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => $this->t('Hello World!'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('access content');
  }

}
