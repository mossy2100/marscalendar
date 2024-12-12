<?php
/**
 * Created by PhpStorm at 2017-08-06T15:50
 *
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 *
 * Functions for calculating the epoch start datetime.
 */

require_once __DIR__ . "/EarthDateTime.php";

/**
 * This class encapsulates a datetime in the Utopian Calendar for Mars.
 *
 * @see http://marscalendar.com
 */
class MarsDateTime {

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Constants

  /**
   * Number of SI seconds in a Martian solar day (sol).
   *
   * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
   */
  const SECONDS_PER_SOL = 88775.244147;

  const SECONDS_PER_DECISOL = self::SECONDS_PER_SOL / 10;

  const SECONDS_PER_CENTISOL = self::SECONDS_PER_SOL / 100;

  const SECONDS_PER_MILLISOL = self::SECONDS_PER_SOL / 1000;

  const SECONDS_PER_MECOND = self::SECONDS_PER_SOL / 1e5;

  const SECONDS_PER_MICROSOL = self::SECONDS_PER_SOL / 1e6;

  const MS_PER_SOL = self::SECONDS_PER_SOL * 1000;

  const MS_PER_DECISOL = self::MS_PER_SOL / 10;

  const MS_PER_CENTISOL = self::MS_PER_SOL / 100;

  const MS_PER_MILLISOL = self::MS_PER_SOL / 1000;

  const MS_PER_MECOND = self::MS_PER_SOL / 1e5;

  const MS_PER_MICROSOL = self::MS_PER_SOL / 1e6;

  /**
   * Number of Terran days in a sol.
   *
   * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
   */
  const DAYS_PER_SOL = 1.0274912517;

  /**
   * Tropical mir length, based on northern vernal equinox,
   * i.e. average number of sols between northern vernal equinoxes.
   */
  const SOLS_PER_TROPICAL_MIR = 668.5907;

  /**
   * Number of days in a mir.
   */
  const DAYS_PER_TROPICAL_MIR = self::DAYS_PER_SOL * self::SOLS_PER_TROPICAL_MIR;

  /**
   * Calendar mir length, which is a close approximation of the tropical mir length.
   */
  const SOLS_PER_CALENDAR_MIR = 668.591;

  const SOLS_PER_DECAMIR = self::SOLS_PER_CALENDAR_MIR * 10;

  const SOLS_PER_HECTOMIR = self::SOLS_PER_CALENDAR_MIR * 100;

  const SOLS_PER_KILOMIR = self::SOLS_PER_CALENDAR_MIR * 1000;

  const SOLS_PER_SHORT_WEEK = 6;

  const SOLS_PER_LONG_WEEK = 7;

  const SOLS_PER_SHORT_MONTH = 27;

  const SOLS_PER_LONG_MONTH = 28;

  const SOLS_PER_SHORT_QUARTER = 167;

  const SOLS_PER_LONG_QUARTER = 168;

  const SOLS_PER_SHORT_MIR = 668;

  const SOLS_PER_LONG_MIR = 669;

  const WEEKS_PER_MONTH = 4;

  const WEEKS_PER_QUARTER = 24;

  const WEEKS_PER_MIR = 96;

  const MONTHS_PER_QUARTER = 6;

  const MONTHS_PER_MIR = 24;

  /**
   * Names and abbreviated names of the Martian months.
   *
   * @var array
   */
  const MONTH_NAMES = [
    1 => ["Phe", "Phoenix", "Phoenix", "00 55.91"],
    ["Cet", "Cetus", "Whale", "01 40.10"],
    ["Dor", "Dorado", "Dolphinfish", "05 14.51"],
    ["Lep", "Lepus", "Hare", "05 33.95"],
    ["Col", "Columba", "Dove", "05 51.76"],
    ["Mon", "Monoceros", "Unicorn", "07 03.63"],
    ["Vol", "Volans", "Flying Fish", "07 47.73"],
    ["Lyn", "Lynx", "Lynx", "07 59.53"],
    ["Cam", "Camelopardalis", "Giraffe", "08 51.37"],
    ["Cha", "Chamaeleon", "Chameleon", "10 41.53"],
    ["Hya", "Hydra", "Sea Serpent", "11 36.73"],
    ["Crv", "Corvus", "Raven", "12 26.52"],
    ["Cen", "Centaurus", "Centaur", "13 04.27"],
    ["Dra", "Draco", "Dragon", "15 08.64"],
    ["Lup", "Lupus", "Wolf", "15 13.21"],
    ["Aps", "Apus", "Bird of Paradise", "16 08.65"],
    ["Pav", "Pavo", "Peacock", "19 36.71"],
    ["Aql", "Aquila", "Eagle", "19 40.02"],
    ["Vul", "Vulpecula", "Fox", "20 13.88"],
    ["Cyg", "Cygnus", "Swan", "20 35.28"],
    ["Del", "Delphinus", "Dolphin", "20 41.61"],
    ["Gru", "Grus", "Crane", "22 27.39"],
    ["Peg", "Pegasus", "Pegasus", "22 41.84"],
    ["Tuc", "Tucana", "Toucan", "23 46.64"],
  ];

  /**
   * Names and abbreviated names of the sols of the week.
   * Following ISO 8601, the week begins with Lunasol (Martian Monday) and ends with Sunsol.
   *
   * @var array
   */
  const SOL_NAMES = [
    1 => "Lunasol",
    "Earthsol",
    "Venusol",
    "Mercurisol",
    "Jupitersol",
    "Saturnsol",
    "Sunsol",
  ];

  /**
   * Default datetime parts.
   *
   * @var array
   */
  const DEFAULTS = [
    'mir'       => 0,
    'month'     => 1,
    'sol'       => 1,
    'millisols' => 0,
    'timezone'  => 0,
  ];

  /**
   * Epoch start datetime.
   *
   * @see http://marscalendar.com/epoch
   * @var array
   */
  const EPOCH_START = [
    'year'     => 1609,
    'month'    => 3,
    'day'      => 12,
    'hour'     => 19,
    'minute'   => 19,
    'second'   => 6,
    'timezone' => 'UTC',
  ];

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Static properties

  /**
   * Cached object for epoch start date.
   *
   * @var EarthDateTime
   */
  protected static $epochStart;

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Instance properties

  /**
   * Timestamp corresponding to the Mars datetime.
   * This is the primary internal representation.
   *
   * @var float
   */
  protected $timestamp;

  /**
   * Cached datetime parts.
   *
   * @var array
   */
  protected $datetimeArray;

  /**
   * The last timestamp that was converted to the cached datetime parts in $datetimeArray.
   *
   * @var float
   */
  protected $lastTimestampConverted;

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Constructor

  /**
   * Constructor.
   *
   * @param null|string|array $datetime
   *    If NULL:     Use default values.
   *    If 'now':    Use current datetime, same as the DateTime constructor.
   *    If a float:  A Unix timestamp.
   *    If an array: A valid Mars datetime array.
   *
   * @throws \Exception
   */
  public function __construct($datetime = NULL) {
    if (is_null($datetime)) {
      $this->fromArray(self::DEFAULTS);
    }
    elseif ($datetime == 'now') {
      $this->setTimestamp(time());
    }
    elseif (is_numeric($datetime)) {
      $this->setTimestamp((float) $datetime);
    }
    elseif (is_array($datetime)) {
      $this->fromArray($datetime);
    }
    else {
      throw new Exception("Invalid paramater for MarsDateTime constructor.");
    }
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Main getters and setters

  /**
   * Get the timestamp.
   *
   * @return float
   */
  public function getTimestamp(): float {
    return $this->timestamp;
  }

  /**
   * Set the timestamp.
   *
   * @param float $timestamp
   *
   * @return self
   */
  public function setTimestamp(float $timestamp): self {
    $this->timestamp = $timestamp;
    return $this;
  }

  /**
   * Get the mir.
   *
   * @return int
   */
  public function getMir(): int {
    $mdt = $this->toArray();
    return $mdt['mir'];
  }

  /**
   * Set the mir.
   *
   * @param int $mir
   *
   * @return self
   */
  public function setMir(int $mir): self {
    $mdt = $this->toArray();
    $mdt['mir'] = $mir;
    return $this->fromArray($mdt);
  }

  /**
   * Get the month.
   *
   * @return int
   */
  public function getMonth(): int {
    $mdt = $this->toArray();
    return $mdt['month'];
  }

  /**
   * Set the month.
   *
   * @param int $month
   *
   * @return self
   */
  public function setMonth(int $month): self {
    $mdt = $this->toArray();
    $mdt['month'] = $month;
    return $this->fromArray($mdt);
  }

  /**
   * Get the sol.
   *
   * @return int
   */
  public function getSol(): int {
    $mdt = $this->toArray();
    return $mdt['sol'];
  }

  /**
   * Set the sol.
   *
   * @param int $sol
   *
   * @return self
   */
  public function setSol(int $sol): self {
    $mdt = $this->toArray();
    $mdt['sol'] = $sol;
    return $this->fromArray($mdt);
  }

  /**
   * Get the millisols.
   *
   * @return float
   */
  public function getMillisols(): float {
    $mdt = $this->toArray();
    return $mdt['millisols'];
  }

  /**
   * Set the millisols.
   *
   * @param float $millisols
   *
   * @return self
   */
  public function setMillisols(float $millisols): self {
    $mdt = $this->toArray();
    $mdt['millisols'] = $millisols;
    return $this->fromArray($mdt);
  }

  /**
   * Get the time zone.
   *
   * @return int
   */
  public function getTimezone(): int {
    $mdt = $this->toArray();
    return $mdt['timezone'];
  }

  /**
   * Set the time zone.
   *
   * @param int $timezone
   *
   * @return self
   * @throws \Exception
   */
  public function setTimezone(int $timezone): self {
    if ($timezone < -5 || $timezone > 5) {
      throw new Exception("Time zone must be an integer in the range -5..5.");
    }
    $mdt = $this->toArray();
    $mdt['timezone'] = $timezone;
    return $this->fromArray($mdt);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Additional handy getters and setters

  /**
   * Get the month name, optionally abbreviated.
   *
   * @param bool $abbrev
   *
   * @return string
   */
  public function getMonthName(bool $abbrev): string {
    $mdt = $this->toArray();
    return self::monthName($mdt['month'], $abbrev);
  }

  /**
   * Get the sol of the mir (1..669).
   *
   * @return int
   */
  public function getSolOfMir(): int {
    $mdt = $this->toArray();
    return self::solOfMir($mdt['month'], $mdt['sol']);
  }

  /**
   * Get the sol of the week (1..7).
   *
   * @return int
   */
  public function getSolOfWeek(): int {
    $mdt = $this->toArray();
    return $mdt['solOfWeek'];
  }

  /**
   * Get the sol name, optionally abbreviated.
   *
   * @param bool $abbrev
   *
   * @return string
   */
  public function getSolName(bool $abbrev): string {
    $mdt = $this->toArray();
    return self::solName($mdt['solOfWeek'], $abbrev);
  }

  /**
   * Get the time zone as a string.
   * (MTC-5, MTC-4, MTC-3, MTC-2, MTC-1, MTC, MTC+1, MTC+2, MTC+3, MTC+4, or MTC+5)
   *
   * @return string
   */
  public function getTimezoneString(): string {
    $result = 'MTC';
    $tz = $this->getTimezone();
    if ($tz != 0) {
      $result .= ($tz < 0 ? '-' : '+') . $tz;
    }
    return $result;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Methods for converting between Julian dates to Utopian datetimes.

  /**
   * Get the datetime as a Julian Date in one of:
   *   - UTC (Coordinated Universal Time) (default)
   *   - TAI (International Atomic Time), or
   *   - TT (Terrestrial Time)
   *
   * @param string $scale
   *
   * @return float
   */
  public function toJulianDate(string $scale = 'UTC'): float {
    $dt_earth = new EarthDateTime('@' . $this->getTimestamp());
    return $dt_earth->toJulianDate($scale);
  }

  /**
   * Constructs a new MarsDateTime object given a Julian date.
   *
   * @param float $jdate
   *
   * @return self
   */
  public static function fromJulianDate(float $jdate): self {
    $edt = EarthDateTime::fromJulianDate($jdate);
    $mdt = new self(self::DEFAULTS);
    $mdt->setTimestamp($edt->getTimestamp());
    return $mdt;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Methods relating to the Mars24 Sunclock (Allison and McEwen, 2000).

  /**
   * Calculate the Mars Sol Date (MSD), which is analogous to the Julian Day count for Mars.
   * It's a running count of sols since December 29, 1873.
   *
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   *
   * @return float
   */
  public function getMarsSolDate(): float {

    $jdtt = $this->toJulianDate('TT');
    return ($jdtt - 2405522.0028779) / self::DAYS_PER_SOL;
  }

  /**
   * Calculate MTC (Coordinated Mars Time), i.e. the local time of day at 0° longitude.
   * Result is a fraction of a sol (i.e. 0.0 = midnight, 0.5 = noon).
   *
   * Note, this should agree with getMillisols() / 1000.
   *
   * @todo TEST
   *
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   *
   * @return float
   */
  public function getMTC(): float {
    $msd = $this->getMarsSolDate();
    return $msd - floor($msd);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * Returns true if a long mir.
   *
   * @param int $mir
   *
   * @return bool
   */
  public static function isLongMir(int $mir): bool {
    // Rules:
    // - All odd mirs are long.
    // - If the mir is divisible by 1000, then it's a long mir.
    // - If the mir is divisible by 100, then it's not a long mir.
    // - If the mir is divisible by 10, then it is a long mir.
    return ($mir % 2 != 0) || ($mir % 1000 == 0) || ($mir % 100 != 0 && $mir % 10 == 0);
  }

  /**
   * Returns number of sols in a given month.
   *
   * @param int $mir
   * @param int $month
   *
   * @return int
   */
  public static function solsInMonth(int $mir, int $month): int {
    if ($month == self::MONTHS_PER_MIR) {
      return self::isLongMir($mir) ? self::SOLS_PER_LONG_MONTH : self::SOLS_PER_SHORT_MONTH;
    }
    return ($month % 6 == 0) ? self::SOLS_PER_SHORT_MONTH : self::SOLS_PER_LONG_MONTH;
  }

  /**
   * Returns number of sols in a given mir.
   *
   * @param int $mir
   *
   * @return int
   */
  public static function solsInMir(int $mir): int {
    return self::isLongMir($mir) ? self::SOLS_PER_LONG_MIR : self::SOLS_PER_SHORT_MIR;
  }

  /**
   * Counts the sols in mirs from 1 to mir (or -mir to -1).
   * $mir must be >= 1
   * Does not count mir 0.
   *
   * This is faster than counting the number of sols in each mir.
   *
   * @param int $mir
   *
   * @returns int
   * @throws \Exception
   */
  protected static function solsInMirsFrom1(int $mir): int {
    if ($mir < 1) {
      throw new Exception("Invalid mir number. Minimum is 1.");
    }

    // Number of intercalary sols due to odd mirs.
    $a = floor(($mir + 1) / 2);

    // Number of intercalary sols due to mirs divisible by 10.
    $b = floor($mir / 10);

    // Number of intercalary sols due to mirs divisible by 100.
    $c = floor($mir / 100);

    // Number of intercalary sols due to mirs divisible by 1000.
    $d = floor($mir / 1000);

    return ($mir * self::SOLS_PER_SHORT_MIR) + $a + $b - $c + $d;
  }

  /**
   * Counts the sols in mirs from $minMir to $maxMir.
   * This is much faster than counting the number of sols in each mir.
   *
   * @param int $minMir
   * @param int $maxMir
   *
   * @returns int
   */
  public static function solsInMirs(int $minMir, int $maxMir): int {
    // Handle simplest case, sols in 1 mir.
    if ($minMir == $maxMir) {
      return self::solsInMir($minMir);
    }

    // Swap the values if necessary.
    if ($minMir > $maxMir) {
      $tmp = $minMir;
      $minMir = $maxMir;
      $maxMir = $tmp;
    }

    if ($minMir < 0) {
      if ($maxMir <= 0) {
        // Long mir pattern is symmetrical around mir 0.
        return self::solsInMirs(-$maxMir, -$minMir);
      }
      else {
        return self::solsInMirsFrom1(-$minMir) + self::solsInMir(0)
          + self::solsInMirsFrom1($maxMir);
      }
    }
    elseif ($minMir == 0) {
      return self::solsInMir(0) + self::solsInMirsFrom1($maxMir);
    }
    elseif ($minMir == 1) {
      return self::solsInMirsFrom1($maxMir);
    }
    else {
      // Number of sols in mirs m..m
      //   = solsInMir(m) + solsInMir(m + 1) ... solsInMir(n - 1) + solsInMir(n)
      //   =   (solsInMir(1) + solsInMir(2) ... solsInMir(n))
      //     - (solsInMir(1) + solsInMir(2) ... solsInMir(m - 1))
      return self::solsInMirsFrom1($maxMir) - self::solsInMirsFrom1($minMir - 1);
    }
  }

  /**
   * Calculate the sol of the mir (1..669).
   *
   * @param int $month
   * @param int $sol
   *
   * @returns int
   */
  public static function solOfMir(int $month, int $sol): int {
    $q = floor(($month - 1) / 6);
    $m = $month - ($q * 6) - 1;
    return ($q * self::SOLS_PER_SHORT_QUARTER) + ($m * self::SOLS_PER_LONG_MONTH) + $sol;
  }

  /**
   * Returns the approximate Julian Date of the northern vernal equinox, using the values
   * calculated from the line of best fit, which was derived from values on
   * http://ops-alaska.com/time/gangale_mst/VernalEquinox.htm
   * (which in turn are taken from Meeus).
   *
   * @param int $mir
   *
   * @return self
   */
  public static function northernVernalEquinox($mir): self {
    $jd = 686.971032958 * $mir + 2308806.29606;
    return MarsDateTime::fromJulianDate($jd);
  }

  /**
   * Returns the month name given the month number (1..24).
   *
   * @param int $month
   * @param bool $abbrev
   *
   * @return string
   */
  public static function monthName(int $month, bool $abbrev = FALSE): string {
    return self::MONTH_NAMES[$month][$abbrev ? 0 : 1];
  }

  /**
   * Returns the sol name given the weeksol number (1..7).
   *
   * @param int $solOfWeek
   * @param bool $abbrev
   *
   * @returns string
   */
  public static function solName(int $solOfWeek, bool $abbrev = FALSE): string {
    $name = self::SOL_NAMES[$solOfWeek];
    return $abbrev ? substr($name, 0, 3) : $name;
  }

  /**
   * Get the epoch as an EarthDateTime.
   *
   * @return EarthDateTime
   */
  public static function epochStart(): EarthDateTime {
    // Check to see if we've already created the epochStart object. If not, do it now.
    if (!isset(self::$epochStart)) {
      self::$epochStart = new EarthDateTime(self::EPOCH_START);
    }
    return self::$epochStart;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Array methods

  /**
   * Get the datetime as an array of datetime parts.
   * Uses the cached datetime parts if still valid, otherwise recalculates them.
   *
   * @return array
   */
  public function toArray(): array {
    // Check if the timestamp has changed.
    if ($this->lastTimestampConverted != $this->timestamp) {
      // Convert the timestamp to number of sols since EPOCH_START.
      $sols = ($this->timestamp - self::epochStart()->getTimestamp()) / self::SECONDS_PER_SOL;

      // Round off to microsols.
      $sols = round($sols * 1e6) / 1e6;

      // Adjust for time zone.
      // The time zone might be set already, and this method shouldn't change it.
      // If it's not set yet, default to 0 (i.e. MTC).
      $timezone = isset($this->datetimeArray['timezone']) ? $this->datetimeArray['timezone'] : 0;
      $sols += $timezone / 10;

      // Get the remainder.
      $origRem = floor($sols);
      $rem = $origRem;

      // Calculate the kilomir.
      // - kilomir -1 is from -1000 to   -1
      // - Kilomir  0 is from     0 to  999
      // - Kilomir  1 is from  1000 to 1999
      $kilomir = 0;
      if ($rem > 0) {
        $kilomir = floor($rem / self::SOLS_PER_KILOMIR);
      }
      else {
        if ($rem < 0) {
          $kilomir = ceil($rem / self::SOLS_PER_KILOMIR) - 1;
        }
      }

      // Adjust so remainder is positive.
      $rem -= $kilomir * self::SOLS_PER_KILOMIR;

      // Calculate the mir.
      $mirs = floor($rem / self::SOLS_PER_CALENDAR_MIR);
      $mir = $kilomir * 1000 + $mirs;
      if ($mirs > 0) {
        $rem -= self::SOLS_PER_LONG_MIR + self::solsInMirsFrom1($mirs - 1);
      }
      $mirLen = self::solsInMir($mir);
      if ($rem >= $mirLen) {
        $rem -= $mirLen;
        $mir++;
      }

      // Calculate the quarter (0..3).
      $month = 1;
      $q = floor($rem / self::SOLS_PER_SHORT_QUARTER);
      if ($q == 4) {
        $q = 3;
      }
      if ($q > 0) {
        $month += $q * 6;
        $rem -= $q * self::SOLS_PER_SHORT_QUARTER;
      }

      // Calculate the month.
      $m = floor($rem / self::SOLS_PER_LONG_MONTH);
      if ($m > 0) {
        $month += $m;
        $rem -= $m * self::SOLS_PER_LONG_MONTH;
      }

      // Calculate sol of the month (1..28).
      // Add 1 because if there are 0 sols remaining we are in the first sol of the month.
      $sol = $rem + 1;

      // Calculate the millisols (rounded down to the nearest microsol).
      $millisols = floor(($sols - $origRem) * 1e6) / 1e3;

      // Update the array of cached datetime parts.
      $this->datetimeArray = [
        'mir'       => $mir,
        'month'     => $month,
        'sol'       => $sol,
        'millisols' => $millisols,
        'timezone'  => $timezone,
      ];

      // Remember that we did this conversion.
      $this->lastTimestampConverted = $this->timestamp;
    }

    return $this->datetimeArray;
  }

  /**
   * Convert any keys from integers to strings, and fill in missing parts with default values.
   * Any invalid keys will be ignored.
   *
   * @param array $datetime
   *
   * @return array
   */
  public static function cleanArray(array $datetime): array {
    // Mir.
    if (isset($datetime['mir'])) {
      $mir = (int) $datetime['mir'];
    }
    elseif (isset($datetime[0])) {
      $mir = (int) $datetime[0];
    }
    else {
      $mir = self::DEFAULTS['mir'];
    }

    // Month.
    if (isset($datetime['month'])) {
      $month = (int) $datetime['month'];
    }
    elseif (isset($datetime[1])) {
      $month = (int) $datetime[1];
    }
    else {
      $month = self::DEFAULTS['month'];
    }

    // Sol of the month.
    if (isset($datetime['sol'])) {
      $sol = (int) $datetime['sol'];
    }
    elseif (isset($datetime[2])) {
      $sol = (int) $datetime[2];
    }
    else {
      $sol = self::DEFAULTS['sol'];
    }

    // Millisols.
    if (isset($datetime['millisols'])) {
      $millisols = (int) $datetime['millisols'];
    }
    elseif (isset($datetime[3])) {
      $millisols = (int) $datetime[3];
    }
    else {
      $millisols = self::DEFAULTS['millisols'];
    }

    // Time zone.
    if (isset($datetime['timezone'])) {
      $timezone = (int) $datetime['timezone'];
    }
    elseif (isset($datetime[4])) {
      $timezone = (int) $datetime[4];
    }
    else {
      $timezone = self::DEFAULTS['timezone'];
    }

    // Create the clean array.
    return [
      'mir'       => $mir,
      'month'     => $month,
      'sol'       => $sol,
      'millisols' => $millisols,
      'timezone'  => $timezone,
    ];
  }

  /**
   * Given a Mars datetime array, updates the internal Unix timestamp.
   *
   * @param array $datetime
   *
   * @return self
   */
  public function fromArray(array $datetime): self {
    // Clean the array.
    $datetime = self::cleanArray($datetime);

    // Count how many sols from the start of the epoch to the start of the given mir.
    if ($datetime['mir'] == 0) {
      // Mir 0.
      $sols = 0;
    }
    elseif ($datetime['mir'] > 0) {
      // Positive mir.
      $sols = self::solsInMirs(0, $datetime['mir'] - 1);
    }
    else {
      // Negative mir.
      $sols = -self::solsInMirs(-$datetime['mir'], -1);
    }

    // Add the sols so far this mir (not counting the given sol).
    $sols += self::solOfMir($datetime['month'], $datetime['sol']) - 1;

    // Add the mils.
    $sols += ($datetime['millisols'] / 1e3);

    // Add time zone offset.
    if ($datetime['timezone']) {
      $sols -= $datetime['timezone'] / 10;
    }

    // Convert to Unix timestamp.
    $this->timestamp = self::epochStart()->getTimestamp() + ($sols * self::SECONDS_PER_SOL);

    // Remember that we did this conversion.
    $this->datetimeArray = $datetime;
    $this->lastTimestampConverted = $this->timestamp;

    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Formatting functions.

  /**
   * Format the date.
   *
   * @param bool $include_prefix
   *
   * @return string
   */
  public function formatDate($include_prefix = TRUE) {
    // Format the parts.
    $mdta = $this->toArray();
    $mir = $mdta['mir'];
    $mm = EarthDateTime::pad($mdta['month']);
    $ss = EarthDateTime::pad($mdta['sol']);
    $result = "{$mir}/{$mm}/{$ss}";

    // Add M prefix if desired.
    if ($include_prefix) {
      $result = 'M' . $result;
    }

    return $result;
  }

  /**
   * Format the time.
   *
   * @param bool $include_prefix
   *
   * @return string
   */
  public function formatTime($include_prefix = TRUE) {
    // Format the millisols.
    $result = sprintf('%07.03F', $this->getMillisols());

    // Add 'M:' prefix if desired.
    if ($include_prefix) {
      $result = 'M:' . $result;
    }

    return $result;
  }

  /**
   * Format the timezone as (+|-)[0-5]00
   * e.g. -500, -400, -300, -200, -100, +000, +100, +200, +300, +400, or +500
   *
   * @return string
   */
  public function formatTimezone() {
    return sprintf('%+04d', $this->getTimezone() * 100);
  }

  /**
   * Format a Mars datetime.
   *
   * @param bool $include_prefix
   * @param bool $include_timezone
   *
   * @return string
   */
  public function formatDatetime($include_prefix = TRUE, $include_timezone = TRUE) {
    $result = $this->formatDate($include_prefix) . ':' . $this->formatTime(FALSE);

    // Add timezone if desired.
    if ($include_timezone) {
      $result .= $this->formatTimezone();
    }

    return $result;
  }
}
