<?php

declare(strict_types=1);

namespace Drupal\utopian\Controller;

use Drupal\utopian\UtopianCalendar;

/**
 * Page callbacks for the Utopian Calendar.
 */
final class UtopianController {

  /**
   * Displays the datetime converter.
   *
   * @return array
   *   A render array for the converter page.
   */
  public function datetimeConverter(): array {
    return [
      '#theme' => 'utopian_datetime_converter',
      '#mars_time_zones' => UtopianCalendar::TIME_ZONES,
      '#attached' => ['library' => ['utopian/converter']],
    ];
  }

  /**
   * Displays the calendar, one page per month.
   *
   * @return array
   *   A render array for the calendar pages.
   */
  public function calendarPages(): array {
    $events = [];
    foreach (UtopianCalendar::EVENTS as $event) {
      $events[] = $event + ['date' => UtopianCalendar::MONTH_NAMES[$event['month']] . ' ' . $event['sol']];
    }

    $solHeadings = [];
    foreach (UtopianCalendar::SOL_NAMES as $solOfWeek => $name) {
      $isWeekend = $solOfWeek > 5;
      $solHeadings[] = [
        'abbreviation' => substr($name, 0, $isWeekend ? 2 : 1),
        'name' => $name,
        'weekend' => $isWeekend,
      ];
    }

    $months = [];
    foreach (UtopianCalendar::MONTH_NAMES as $month => $name) {
      $months[] = [
        'number' => $month,
        'name' => $name,
        'season' => UtopianCalendar::SEASONS[intdiv($month - 1, UtopianCalendar::MONTHS_PER_QUARTER)],
        'image' => base_path() . 'sites/default/media/images/constellations/' . $name . '.png',
        'weeks' => $this->monthWeeks($month),
      ];
    }

    return [
      '#theme' => 'utopian_calendar_pages',
      '#events' => $events,
      '#sol_headings' => $solHeadings,
      '#months' => $months,
      '#attached' => ['library' => ['utopian/calendar_pages']],
    ];
  }

  /**
   * Builds the sol squares for one month page.
   *
   * @param int $month
   *   The month (1..24).
   *
   * @return array
   *   Rows of 7 cells, each with a 'label' (the sol number, or NULL for an
   *   empty square) and a list of CSS 'classes'.
   */
  private function monthWeeks(int $month): array {
    $eventClasses = [];
    foreach (UtopianCalendar::EVENTS as $event) {
      if ($event['month'] === $month) {
        $eventClasses[$event['sol']] = ['special-sol', $event['class']];
      }
    }

    $isQuarterEnd = $month % UtopianCalendar::MONTHS_PER_QUARTER === 0;
    $lastSol = UtopianCalendar::SOLS_PER_WEEK * UtopianCalendar::WEEKS_PER_MONTH;
    $weeks = [];
    for ($week = 0; $week < UtopianCalendar::WEEKS_PER_MONTH; $week++) {
      $cells = [];
      for ($solOfWeek = 1; $solOfWeek <= UtopianCalendar::SOLS_PER_WEEK; $solOfWeek++) {
        $sol = $week * UtopianCalendar::SOLS_PER_WEEK + $solOfWeek;
        $classes = $solOfWeek > 5 ? ['weekend'] : [];
        $label = $sol;

        if ($sol === $lastSol && $isQuarterEnd) {
          // The last month of each quarter is short, except Tucana, whose 28th
          // sol is the intercalary sol of a long mir.
          if ($month === count(UtopianCalendar::MONTH_NAMES)) {
            $classes = [...$classes, 'special-sol', 'intercalary'];
          }
          else {
            $classes = [...$classes, 'empty'];
            $label = NULL;
          }
        }
        elseif (isset($eventClasses[$sol])) {
          $classes = [...$classes, ...$eventClasses[$sol]];
        }

        $cells[] = ['label' => $label, 'classes' => $classes];
      }
      $weeks[] = $cells;
    }
    return $weeks;
  }

}
