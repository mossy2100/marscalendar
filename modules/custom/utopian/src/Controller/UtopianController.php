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
   * Displays the datetime converter.
   *
   * @return array
   *   A render array representing the datetime converter page content.
   */
  public function datetimeConverter() {
    $build = [
      '#theme' => 'utopian_datetime_converter',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    return $build;
  }

  /**
   * Displays the calendar pages.
   *
   * @return array
   *   A render array representing the calendar pages content.
   */
  public function calendarPages() {
    ob_start();

    $month_names = [
      1 => 'Phoenix',
      'Cetus',
      'Dorado',
      'Lepus',
      'Columba',
      'Monoceros',
      'Volans',
      'Lynx',
      'Camelopardalis',
      'Chameleon',
      'Hydra',
      'Corvus',
      'Centaurus',
      'Draco',
      'Lupus',
      'Apus',
      'Pavo',
      'Aquila',
      'Vulpecula',
      'Cygnus',
      'Delphinus',
      'Grus',
      'Pegasus',
      'Tucana',
    ];

    $day_names = [
      1 => 'Lunasol',
      'Earthsol',
      'Mercurisol',
      'Jupitersol',
      'Venusol',
      'Saturnsol',
      'Sunsol',
    ];

    $seasons = ['spring', 'summer', 'autumn', 'winter'];

    foreach ($month_names as $m => $month) {
      // Get the season class.
      $season = $seasons[floor(($m - 1) / 6)];

      echo "<table class='calendar-page $season'>\n";
      echo "<thead>\n";
      echo "  <tr>\n";
      echo "    <th class='monthName' colspan='7'>$m. $month</th>\n";
      echo "  </tr>\n";
      echo "</thead>\n";
      echo "<tbody>\n";
      echo "  <tr>\n";
      echo "    <td class='monthIcon' colspan='7'><img src='/sites/default/files/month-icons/$month.png'></td>\n";
      echo "  </tr>\n";

      // Days of the week header.
      echo "  <tr class='daysOfWeek squares'>\n";
      for ($dow = 1; $dow <= 7; $dow++) {
        $abbrev = substr($day_names[$dow], 0, $dow <= 5 ? 1 : 2);
        echo "    <th>$abbrev</th>\n";
      }
      echo "  </tr>\n";

      // Date squares.
      for ($w = 1; $w <= 4; $w++) {
        echo "  <tr class='squares'>\n";
        for ($dow = 1; $dow <= 7; $dow++) {
          $classes = [];

          $sol = ($w - 1) * 7 + $dow;
          $td = $sol;

          // Add classes for special sols.
          if ($m == 1 && $sol == 1) {
            $classes[] = 'special-sol';
            $classes[] = 'vernal-equinox';
          }
          elseif ($m == 6 && $sol == 12) {
            $classes[] = 'special-sol';
            $classes[] = 'aphelion';
          }
          elseif ($m == 6 && $sol == 28) {
            $classes[] = 'empty';
            $td = '&nbsp;';
          }
          elseif ($m == 7 && $sol == 27) {
            $classes[] = 'special-sol';
            $classes[] = 'summer-solstice';
          }
          elseif ($m == 12 && $sol == 28) {
            $classes[] = 'empty';
            $td = '&nbsp;';
          }
          elseif ($m == 14 && $sol == 11) {
            $classes[] = 'special-sol';
            $classes[] = 'autumnal-equinox';
          }
          elseif ($m == 18 && $sol == 12) {
            $classes[] = 'special-sol';
            $classes[] = 'perihelion';
          }
          elseif ($m == 18 && $sol == 28) {
            $classes[] = 'empty';
            $td = '&nbsp;';
          }
          elseif ($m == 19 && $sol == 14) {
            $classes[] = 'special-sol';
            $classes[] = 'winter-solstice';
          }
          elseif ($m == 24 && $sol == 28) {
            $classes[] = 'special-sol';
            $classes[] = 'intercalary';
          }
          $classes_str = $classes ? ' class="' . implode(' ', $classes) . '"'
            :'';

          echo "    <td{$classes_str}>$td</td>\n";
        }

        echo "  </tr>\n";
      }

      echo "</tbody>\n";
      echo "</table>\n";
    }
    $calendar_pages = ob_get_clean();

    $build = [
      '#theme'     => 'utopian_calendar_pages',
      '#calendar_pages' => $calendar_pages,
      '#cache'     => [
        'max-age' => 0,
      ],
    ];
    return $build;
  }
}
