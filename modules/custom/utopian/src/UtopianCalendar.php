<?php

declare(strict_types=1);

namespace Drupal\utopian;

/**
 * Names and nominal dates for the Utopian Calendar.
 *
 * The date arithmetic lives in js/utopian-time.js. This class holds only the
 * data needed to render pages server-side.
 */
final class UtopianCalendar {

  /**
   * Month names (constellations), indexed 1..24.
   */
  public const MONTH_NAMES = [
    1 => 'Phoenix',
    2 => 'Cetus',
    3 => 'Dorado',
    4 => 'Lepus',
    5 => 'Columba',
    6 => 'Monoceros',
    7 => 'Volans',
    8 => 'Lynx',
    9 => 'Camelopardalis',
    10 => 'Chamaeleon',
    11 => 'Hydra',
    12 => 'Corvus',
    13 => 'Centaurus',
    14 => 'Draco',
    15 => 'Lupus',
    16 => 'Apus',
    17 => 'Pavo',
    18 => 'Aquila',
    19 => 'Vulpecula',
    20 => 'Cygnus',
    21 => 'Delphinus',
    22 => 'Grus',
    23 => 'Pegasus',
    24 => 'Tucana',
  ];

  /**
   * Sol names, indexed 1..7.
   *
   * The order parallels the Terran days of the week (Monday..Sunday).
   */
  public const SOL_NAMES = [
    1 => 'Lunasol',
    2 => 'Earthsol',
    3 => 'Mercurisol',
    4 => 'Jupitersol',
    5 => 'Venusol',
    6 => 'Saturnsol',
    7 => 'Sunsol',
  ];

  /**
   * Mars time zone labels, keyed by offset in zodes from MTC.
   */
  public const TIME_ZONES = [
    -5 => 'MTC-5 (Amazonis)',
    -4 => 'MTC-4 (Olympus)',
    -3 => 'MTC-3 (Tharsis)',
    -2 => 'MTC-2 (Marineris)',
    -1 => 'MTC-1 (Argyre)',
    0 => 'MTC (Noachis)',
    1 => 'MTC+1 (Arabia)',
    2 => 'MTC+2 (Hellas)',
    3 => 'MTC+3 (Utopia)',
    4 => 'MTC+4 (Elysium)',
    5 => 'MTC+5 (Arcadia)',
  ];

  /**
   * Nominal dates of the annual astronomical events, in calendar order.
   *
   * Each entry has the event's CSS class, description, month (1..24) and sol
   * of the month (1..28).
   */
  public const EVENTS = [
    ['class' => 'vernal-equinox', 'label' => 'Northern vernal/southern autumnal equinox', 'month' => 1, 'sol' => 1],
    ['class' => 'aphelion', 'label' => 'Aphelion', 'month' => 6, 'sol' => 12],
    ['class' => 'summer-solstice', 'label' => 'Northern summer/southern winter solstice', 'month' => 7, 'sol' => 27],
    ['class' => 'autumnal-equinox', 'label' => 'Northern autumnal/southern vernal equinox', 'month' => 14, 'sol' => 11],
    ['class' => 'perihelion', 'label' => 'Perihelion', 'month' => 18, 'sol' => 12],
    ['class' => 'winter-solstice', 'label' => 'Northern winter/southern summer solstice', 'month' => 19, 'sol' => 14],
  ];

  /**
   * Seasons (northern hemisphere), one per quarter of 6 months.
   */
  public const SEASONS = ['spring', 'summer', 'autumn', 'winter'];

  public const MONTHS_PER_QUARTER = 6;

  public const SOLS_PER_WEEK = 7;

  public const WEEKS_PER_MONTH = 4;

}
