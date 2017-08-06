<?php
/**
 * Created by PhpStorm at 2017-08-06T19:51
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)

 * @file
 * Contains \Drupal\utopian\Controller\UtopianController.
 */

namespace Drupal\utopian\Controller;

/**
 * Controller routines for Utopian routes.
 */
class UtopianController {

  /**
   * Displays the converter.
   *
   * @return array
   *   A render array representing the converter page content.
   */
  public function converter() {
//    $html = file_get_contents(DRUPAL_ROOT . '/sites/default/files/html/converter.html');
//
//    var_dump($html);

    $build = array(
//      '#type' => 'markup',
      '#theme' => 'utopian_converter',
      '#cache' => [
        'max-age' => 0,
      ],
    );
    return $build;
  }
}
