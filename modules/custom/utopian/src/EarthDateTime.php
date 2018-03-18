<?php

/**
 * This class extends and improve PHP's built-in DateTime class.
 * This version is for PHP 7.1 and higher.
 *
 * Shaun Moss (shaun@astromultimedia.com)
 */

class EarthDateTime extends DateTime {

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Constants

  // These values are calculated from average Gregorian calendar month and
  // year lengths (365-366 days/year, and 97 leap years per 400).
  const SECONDS_PER_MINUTE = 60;

  const SECONDS_PER_HOUR = 3600;

  const SECONDS_PER_DAY = 86400;

  const SECONDS_PER_WEEK = 604800;

  const SECONDS_PER_MONTH = 2629746;

  const SECONDS_PER_YEAR = 31556952;

  const MINUTES_PER_HOUR = 60;

  const MINUTES_PER_DAY = 1440;

  const MINUTES_PER_WEEK = 10080;

  const MINUTES_PER_MONTH = 43829.1;

  const MINUTES_PER_YEAR = 525949.2;

  const HOURS_PER_DAY = 24;

  const HOURS_PER_WEEK = 168;

  const HOURS_PER_MONTH = 730.485;

  const HOURS_PER_YEAR = 8765.82;

  const DAYS_PER_WEEK = 7;

  const DAYS_PER_MONTH = 30.436875;

  const DAYS_PER_YEAR = 365.2425;

  const WEEKS_PER_MONTH = 4.348125;

  const WEEKS_PER_YEAR = 52.1775;

  const MONTHS_PER_YEAR = 12;

  // For calculating the datetime in different scales.
  const TT_MINUS_TAI = 32.184;

  const JD_MINUS_UNIX = 2440587.5;

  /**
   * Default datetime parts.
   *
   * @var array
   */
  const DEFAULTS = [
    'year'        => 1970,
    'month'       => 1,
    'day'         => 1,
    'hour'        => 0,
    'minute'      => 0,
    'second'      => 0,
    'microsecond' => 0,
    'timezone'    => NULL,
  ];

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Constructor

  /**
   * Constructor for making dates and datetimes.
   *
   * Datetimes can be provided as valid strings or arrays, or NULL for default values.
   * Time zones may be provided as DateTimeZone objects or valid time zone strings.
   *
   * @see http://php.net/manual/en/datetime.construct.php
   *
   * @param null|string|array $datetime
   *    If NULL:      Use default values.
   *    If a string:  Same as the DateTime constructor.
   *    If an array:  A valid datetime array.
   * @param DateTimeZone|string|null $timezone
   *
   * @throws \Exception
   */
  public function __construct($datetime = NULL, $timezone = NULL) {
    // Handle default parameter.
    if (is_null($datetime)) {
      $datetime = self::DEFAULTS;
    }

    // Convert array parameter to string.
    if (is_array($datetime)) {
      // Use the $timezone parameter if provided, and the time zone is not set in the array.
      if (!isset($datetime['timezone']) && !isset($datetime[7]) && isset($timezone)) {
        $datetime['timezone'] = $timezone;
      }
      // Set the timezone parameter to NULL, as, if specified, it's now in the array.
      $timezone = NULL;
      // Convert the array to a string.
      $datetime = self::arrayToString($datetime);
    }

    // Check we have a string.
    if (!is_string($datetime)) {
      var_dump($datetime);
      throw new Exception("Invalid parameter type for EarthDateTime constructor.");
    }

    // Convert timezone to DateTimeZone.
    $timezone = self::convertTimezone($timezone);

    // Call parent constructor:
    parent::__construct($datetime, $timezone);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods for creating new EarthDateTime objects.

  /**
   * The current datetime as an EarthDateTime object.
   *
   * @return self
   */
  public static function now(): self {
    // This will call the parent constructor, which defaults to 'now'.
    return new static();
  }

  /**
   * Today's date as an EarthDateTime object.
   *
   * @return self
   */
  public static function today(): self {
    $now = self::now();
    return $now->setTime(0, 0, 0, 0);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // String functions.

  /**
   * Pads a number with '0' characters up to a specified width.
   *
   * @param int $n
   * @param int $w
   *
   * @return string
   */
  public static function pad(int $n, int $w = 2): string {
    return str_pad((int) $n, $w, '0', STR_PAD_LEFT);
  }

  /**
   * Convert the datetime to a string.
   *
   * @return string
   */
  public function __toString(): string {
    return $this->format('Y-m-d H:i:s P e');
  }

  /**
   * Return the provided parameter as a DateTimeZone object or NULL, if not already.
   *
   * @param string|DateTimeZone|null $timezone
   *
   * @return \DateTimeZone|null
   * @throws \Exception
   */
  public static function convertTimezone($timezone): ?DateTimeZone {
    // Convert string time zone to object.
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    // Check we have a valid timezone.
    if (!is_null($timezone) && !($timezone instanceof DateTimeZone)) {
      throw new Exception("Invalid parameter type for \$timezone. Must be DateTimeZone or a valid time zone string.");
    }

    return $timezone;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Array methods.

  /**
   * Convert any keys from integers to strings, and fill in missing parts with default values.
   * Any invalid keys will be ignored.
   *
   * @param array $datetime
   *
   * @return array
   */
  public static function cleanArray(array $datetime): array {
    // Year.
    if (isset($datetime['year'])) {
      $year = (int) $datetime['year'];
    }
    elseif (isset($datetime[0])) {
      $year = (int) $datetime[0];
    }
    else {
      $year = self::DEFAULTS['year'];
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

    // Day of the month.
    if (isset($datetime['day'])) {
      $day = (int) $datetime['day'];
    }
    elseif (isset($datetime[2])) {
      $day = (int) $datetime[2];
    }
    else {
      $day = self::DEFAULTS['day'];
    }

    // Hour.
    if (isset($datetime['hour'])) {
      $hour = (int) $datetime['hour'];
    }
    elseif (isset($datetime[3])) {
      $hour = (int) $datetime[3];
    }
    else {
      $hour = self::DEFAULTS['hour'];
    }

    // Minute.
    if (isset($datetime['minute'])) {
      $minute = (int) $datetime['minute'];
    }
    elseif (isset($datetime[4])) {
      $minute = (int) $datetime[4];
    }
    else {
      $minute = self::DEFAULTS['minute'];
    }

    // Second.
    if (isset($datetime['second'])) {
      $second = (int) $datetime['second'];
    }
    elseif (isset($datetime[5])) {
      $second = (int) $datetime[5];
    }
    else {
      $second = self::DEFAULTS['second'];
    }

    // Microsecond.
    if (isset($datetime['microsecond'])) {
      $microsecond = (int) $datetime['microsecond'];
    }
    elseif (isset($datetime[6])) {
      $microsecond = (int) $datetime[6];
    }
    else {
      $microsecond = self::DEFAULTS['microsecond'];
    }

    // Time zone.
    if (isset($datetime['timezone'])) {
      $timezone = self::convertTimezone($datetime['timezone']);
    }
    elseif (isset($datetime[7])) {
      $timezone = self::convertTimezone($datetime[7]);
    }
    else {
      $timezone = self::DEFAULTS['timezone'];
    }

    // Create the clean array.
    return [
      'year'        => $year,
      'month'       => $month,
      'day'         => $day,
      'hour'        => $hour,
      'minute'      => $minute,
      'second'      => $second,
      'microsecond' => $microsecond,
      'timezone'    => $timezone,
    ];
  }

  /**
   * Convert a datetime array to a datetime string.
   * Any date or time parts can be omitted to set them to the default value.
   *
   * The array can have (case-sensitive) string keys. String keys with default values:
   *    [
   *      'year'        => 1970,
   *      'month'       => 1,
   *      'day'         => 1,
   *      'hour'        => 0,
   *      'minute'      => 0,
   *      'second'      => 0,
   *      'microsecond' => 0,
   *      'timezone'    => NULL,
   *    ]
   *
   * The array can also have integer keys, as a convenient shorthand.
   *    0 => year, 1 => month, 2 => day, 3 => hour, 4 => minute, 5 => second, 6 => microsecond
   * e.g.
   *    Just the date:
   *      [1970, 1, 1]
   *    Specify date and time parts to the second:
   *      [1970, 1, 1, 0, 0, 0]
   *    Specify date and time parts to the microsecond:
   *      [1970, 1, 1, 0, 0, 0, 0]
   *    Specify date and time parts to the microsecond with timezone:
   *      [1970, 1, 1, 0, 0, 0, 0, 'UTC']
   *
   * String keys take precedence over integer keys.
   *
   * @param array $datetime
   *
   * @return string
   */
  public static function arrayToString(array $datetime): string {
    // Convert integer keys to string keys.
    $datetime = self::cleanArray($datetime);

    // Get the parts as strings.
    $yyyy = self::pad($datetime['year'], 4);
    $mm = self::pad($datetime['month']);
    $dd = self::pad($datetime['day']);
    $hh = self::pad($datetime['hour']);
    $ii = self::pad($datetime['minute']);
    $ss = self::pad($datetime['second']);
    $micros = self::pad($datetime['microsecond'], 6);
    $timezone = is_null($datetime['timezone']) ? '' : (' ' . $datetime['timezone']->getName());

    // Return the ISO string.
    return "{$yyyy}-{$mm}-{$dd}T{$hh}:{$ii}:{$ss}.{$micros}{$timezone}";
  }

  /**
   * Return the datetime as an array of datetime parts.
   *
   * @return array
   */
  public function toArray(): array {
    return
      [
        'year'        => $this->getYear(),
        'month'       => $this->getMonth(),
        'day'         => $this->getDay(),
        'hour'        => $this->getHour(),
        'minute'      => $this->getMinute(),
        'second'      => $this->getSecond(),
        'microsecond' => $this->getMicrosecond(),
        'timezone'    => $this->getTimezone(),
      ];
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Getters and setters for datetime parts.

  /**
   * Get the year.
   *
   * @return int
   */
  public function getYear(): int {
    return (int) $this->format('Y');
  }

  /**
   * Set the year.
   *
   * @param int $year
   *
   * @return self
   */
  public function setYear(int $year): self {
    return $this->setDate($year, $this->getMonth(), $this->getDay());
  }

  /**
   * Get the month.
   *
   * @return int
   */
  public function getMonth(): int {
    return (int) $this->format('n');
  }

  /**
   * Set the month.
   *
   * @param int $month
   *
   * @return self
   */
  public function setMonth(int $month): self {
    return $this->setDate($this->getYear(), $month, $this->getDay());
  }

  /**
   * Get the day of the month.
   *
   * @return int
   */
  public function getDay(): int {
    return (int) $this->format('j');
  }

  /**
   * Set the day of the month.
   *
   * @param int $day
   *
   * @return self
   */
  public function setDay(int $day): self {
    return $this->setDate($this->getYear(), $this->getMonth(), $day);
  }

  /**
   * Get the hour.
   *
   * @return int
   */
  public function getHour(): int {
    return (int) $this->format('G');
  }

  /**
   * Set the hour.
   *
   * @param int $hour
   *
   * @return self
   */
  public function setHour(int $hour): self {
    return $this->setTime($hour, $this->getMinute(), $this->getSecond(), $this->getMicrosecond());
  }

  /**
   * Get the minute.
   *
   * @return int
   */
  public function getMinute(): int {
    return (int) $this->format('i');
  }

  /**
   * Set the minute.
   *
   * @param int $minute
   *
   * @return self
   */
  public function setMinute(int $minute): self {
    return $this->setTime($this->getHour(), $minute, $this->getSecond(), $this->getMicrosecond());
  }

  /**
   * Get the second.
   *
   * @return int
   */
  public function getSecond(): int {
    return (int) $this->format('s');
  }

  /**
   * Set the second.
   *
   * @param int $second
   *
   * @return self
   */
  public function setSecond(int $second): self {
    return $this->setTime($this->getHour(), $this->getMinute(), $second, $this->getMicrosecond());
  }

  /**
   * Get the microsecond.
   *
   * @return int
   */
  public function getMicrosecond(): int {
    return (int) $this->format('u');
  }

  /**
   * Set the microsecond.
   *
   * @param int $microsecond
   *
   * @return self
   */
  public function setMicrosecond(int $microsecond): self {
    return $this->setTime($this->getHour(), $this->getMinute(), $this->getSecond(), $microsecond);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Additional handy getters and setters.

  /**
   * Get the day of the year as an integer (1..366).
   *
   * @return int
   */
  public function getDayOfYear(): int {
    return ((int) $this->format('z')) + 1;
  }

  /**
   * Get the week of the year as an integer (1.. 52).
   *
   * @return int
   */
  public function getWeek(): int {
    return (int) $this->format('W');
  }

  /**
   * Get the day of the week as an integer (1..7).
   * 1 = Monday .. 7 = Sunday
   *
   * @return int
   */
  public function getDayOfWeek(): int {
    return (int) $this->format('N');
  }

  /**
   * Set the timezone. Unlike the parent method, accepts strings.
   *
   * @param DateTimeZone|string|null $timezone
   *
   * @return self
   */
  public function setTimezone($timezone) {
    $timezone = self::convertTimezone($timezone);
    parent::setTimezone($timezone);
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Methods for adding and subtracting durations.
  // These methods return a new EarthDateTime object; they don't modify the calling object.

  /**
   * Constructs a DateInterval object from an array of datetime parts.
   *
   * @param array $array
   *
   * @return DateInterval
   */
  public static function createInterval(array $array): DateInterval {
    $interval = new DateInterval('P0D');
    $interval->y = isset($array['year']) ? (int) $array['year'] : 0;
    $interval->m = isset($array['month']) ? (int) $array['month'] : 0;
    $interval->d = isset($array['day']) ? (int) $array['day'] : 0;
    $interval->h = isset($array['hour']) ? (int) $array['hour'] : 0;
    $interval->i = isset($array['minute']) ? (int) $array['minute'] : 0;
    $interval->s = isset($array['second']) ? (int) $array['second'] : 0;
    $interval->f = isset($array['microsecond']) ? (float) $array['microsecond'] : 0;
    return $interval;
  }

  /**
   * Add a period of time.
   *
   * Wraps DateTime::add(). The datetime parts are provided as an array, same as createFromArray(),
   * and the DateInterval object is created automatically.
   *
   * @param array $array
   *
   * @return self
   */
  public function addTime(array $array): self {
    return $this->add(EarthDateTime::createInterval($array));
  }

  /**
   * Subtract a period of time.
   *
   * @param array $array
   *
   * @return self
   */
  public function subTime(array $array): self {
    return $this->sub(EarthDateTime::createInterval($array));
  }

  /**
   * Add years.
   *
   * @param int $years
   *
   * @return self
   */
  public function addYears(int $years): self {
    return $this->addTime(['years' => $years]);
  }

  /**
   * Subtract years.
   *
   * @param int $years
   *
   * @return self
   */
  public function subYears(int $years): self {
    return $this->subTime(['years' => $years]);
  }

  /**
   * Add months.
   *
   * @param int $months
   *
   * @return self
   */
  public function addMonths(int $months): self {
    return $this->addTime(['months' => $months]);
  }

  /**
   * Subtract months.
   *
   * @param int $months
   *
   * @return self
   */
  public function subMonths(int $months): self {
    return $this->subTime(['months' => $months]);
  }

  /**
   * Add weeks.
   *
   * @param int $weeks
   *
   * @return self
   */
  public function addWeeks(int $weeks): self {
    return $this->addDays($weeks * 7);
  }

  /**
   * Subtract weeks.
   *
   * @param int $weeks
   *
   * @return self
   */
  public function subWeeks(int $weeks): self {
    return $this->subDays($weeks * 7);
  }

  /**
   * Add days.
   *
   * @param int $days
   *
   * @return self
   */
  public function addDays(int $days): self {
    return $this->addTime(['days' => $days]);
  }

  /**
   * Subtract days.
   *
   * @param int $days
   *
   * @return self
   */
  public function subDays(int $days): self {
    return $this->subTime(['days' => $days]);
  }

  /**
   * Add hours.
   *
   * @param int $hours
   *
   * @return self
   */
  public function addHours(int $hours): self {
    return $this->addTime(['hours' => $hours]);
  }

  /**
   * Subtract hours.
   *
   * @param int $hours
   *
   * @return self
   */
  public function subHours(int $hours): self {
    return $this->subTime(['hours' => $hours]);
  }

  /**
   * Add minutes.
   *
   * @param int $minutes
   *
   * @return self
   */
  public function addMinutes(int $minutes): self {
    return $this->addTime(['minutes' => $minutes]);
  }

  /**
   * Subtract minutes.
   *
   * @param int $minutes
   *
   * @return self
   */
  public function subMinutes(int $minutes): self {
    return $this->subTime(['minutes' => $minutes]);
  }

  /**
   * Add seconds.
   *
   * @param int $seconds
   *
   * @return self
   */
  public function addSeconds(int $seconds): self {
    return $this->addTime(['seconds' => $seconds]);
  }

  /**
   * Subtract seconds.
   *
   * @param int $seconds
   *
   * @return self
   */
  public function subSeconds(int $seconds): self {
    return $this->subTime(['seconds' => $seconds]);
  }

  /**
   * Add microseconds.
   *
   * @param int $microseconds
   *
   * @return self
   */
  public function addMicroseconds(int $microseconds): self {
    return $this->addTime(['microseconds' => $microseconds]);
  }

  /**
   * Subtract microseconds.
   *
   * @param int $microseconds
   *
   * @return self
   */
  public function subMicroseconds(int $microseconds): self {
    return $this->subTime(['microseconds' => $microseconds]);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Miscellaneous useful functions.

  /**
   * Generates a string describing the difference in time between two datetimes.
   *
   * @param self $dt
   *
   * @return string
   */
  public function diffApproxText(self $dt = NULL): string {
    $ts1 = $this->getTimestamp();
    $ts2 = is_null($dt) ? time() : $dt->getTimestamp();

    // Get the time difference in seconds.
    $seconds = abs($ts1 - $ts2);

    // Seconds.
    if ($seconds <= 20) {
      return $seconds == 1 ? 'a second' : "$seconds seconds";
    }

    // 5 seconds.
    if ($seconds < 58) {
      return (round($seconds / 5) * 5) . ' seconds';
    }

    // Minutes.
    $minutes = round($seconds / self::SECONDS_PER_MINUTE);
    if ($minutes <= 20) {
      return $minutes == 1 ? 'a minute' : "$minutes minutes";
    }

    // 5 minutes.
    if ($minutes < 58) {
      return (round($minutes / 5) * 5) . ' minutes';
    }

    // Hours.
    $hours = round($seconds / self::SECONDS_PER_HOUR);
    if ($hours < 48 && $hours % self::HOURS_PER_DAY != 0) {
      return $hours == 1 ? 'an hour' : "$hours hours";
    }

    // Days.
    $days = round($seconds / self::SECONDS_PER_DAY);
    if ($days < 28 && $days % self::DAYS_PER_WEEK != 0) {
      return $days == 1 ? 'a day' : "$days days";
    }

    // Weeks.
    $weeks = round($seconds / self::SECONDS_PER_WEEK);
    if ($weeks <= 12) {
      return $weeks == 1 ? 'a week' : "$weeks weeks";
    }

    // Months:
    $months = round($seconds / self::SECONDS_PER_MONTH);
    if ($months < 24 && $months % self::MONTHS_PER_YEAR != 0) {
      return $months == 1 ? 'a month' : "$months months";
    }

    // Years.
    $years = round($seconds / self::SECONDS_PER_YEAR);
    return $years == 1 ? 'a year' : "$years years";
  }

  /**
   * Calculate the difference in seconds between two datetimes.
   *
   * The signature is identical to DateTime::diff(), except that it returns the
   * difference in seconds rather than as a DateInterval.
   *
   * @param self $dt
   * @param bool $absolute
   *   If TRUE then the absolute value of the difference is returned.
   *
   * @return int
   * @internal param \EarthDateTime $dt2
   */
  public function diffSeconds(self $dt, bool $absolute = FALSE): int {
    $diff = $this->getTimestamp() - $dt->getTimestamp();
    if ($absolute) {
      $diff = abs($diff);
    }
    return $diff;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion functions between Gregorian and Julian dates.

  /**
   * Construct a new EarthDateTime from a Julian Date (UTC).
   *
   * Code adapted from http://www.onlineconversion.com/julian_date.htm
   * Algorithm adapted from Press et al - "Numerical Recipes in C, 2nd ed."
   *
   * This is a variation from the original code, which supports Julian Calendar dates.
   * However, PHP uses the Proleptic Gregorian Calendar.
   *
   * I haven't used jdtogregorian() or jdtounix() because these functions only support dates
   * (i.e. Julian Day Count), not times.
   *
   * This method can be used as an alternative constructor.
   * If you want to set the timezone, call setTimezone() on the result object.
   *
   * @param float $jdate
   *
   * @return array
   */
  public static function julianDateToArray(float $jdate): array {
    // Get the whole number of days (Julian Day) and the fractional part.
    $jday = floor($jdate);
    $frac = $jdate - $jday;

    // Calculate intermediate variables.
    $j0 = floor((($jday - 1867216) - 0.25) / 36524.25);
    $j1 = $jday + 1 + $j0 - floor(0.25 * $j0);

    // Correction for half day offset.
    $frac += 0.5;
    if ($frac >= 1.0) {
      $frac -= 1.0;
      ++$j1;
    }

    // Calculate intermediate variables.
    $j2 = $j1 + 1524;
    $j3 = floor(6680.0 + (($j2 - 2439870) - 122.1) / 365.25);
    $j4 = floor($j3 * 365.25);
    $j5 = floor(($j2 - $j4) / 30.6001);

    // Get day, month, year.
    $day = floor($j2 - $j4 - floor($j5 * 30.6001));
    $month = floor($j5 - 1);
    if ($month > 12) {
      $month -= 12;
    }
    $year = floor($j3 - 4715);
    if ($month > 2) {
      --$year;
    }

    // Get time of day from day fraction.
    $hours = $frac * 24.0;
    $hour = floor($hours);
    $minutes = ($hours - $hour) * 60.0;
    $minute = floor($minutes);
    $seconds = ($minutes - $minute) * 60.0;
    $second = floor($seconds);
    $microseconds = ($seconds - $second) * 1e6;
    $microsecond = floor($microseconds);

    // Return the result array.
    return [$year, $month, $day, $hour, $minute, $second, $microsecond];
  }

  /**
   * Constructs a new EarthDateTime object given a Julian date.
   *
   * @param float $jdate
   *
   * @return self
   */
  public static function fromJulianDate(float $jdate): self {
    return new static(self::julianDateToArray($jdate), 'UTC');
  }

  /**
   * Convert a Julian Date to a Unix timestamp.
   * (Ignores issues with the limited range of the Unix timestamp on 32-bit systems.)
   *
   * @param float $jd
   *
   * @return float
   */
  public static function julianDateToTimestamp(float $jd): float {
    return ($jd - self::JD_MINUS_UNIX) * self::SECONDS_PER_DAY;
  }

  /**
   * Get the datetime as a Julian Date in one of:
   *   - UTC (Coordinated Universal Time) (default)
   *   - TAI (International Atomic Time), or
   *   - TT (Terrestrial Time)
   *
   * Note, on 32-bit systems, the value returned by getTimestamp() will be wrong for datetimes
   * outside the range 1970-01-01T00:00:00 .. 2038-01-19T03:14:07
   * Thus the result of this function would be wrong.
   * Not necessary to fix right now. Assume 64-bit for now.
   * Could use code from http://www.onlineconversion.com/julian_date.htm to avoid this issue.
   *
   * @param string $scale
   *
   * @return float
   * @throws \Exception
   */
  public function toJulianDate(string $scale = 'UTC'): float {
    $t = $this->getTimestamp();

    switch ($scale) {
      case 'UTC':
        $d = 0;
        break;

      case 'TAI':
        $d = $this->taiMinusUtc();
        break;

      case 'TT':
        $d = $this->ttMinusUtc();
        break;

      default:
        throw new Exception("Scale must be 'UTC', 'TAI', or 'TT', or omit for UTC.");
        break;
    }

    return ($t + $d) / self::SECONDS_PER_DAY + self::JD_MINUS_UNIX;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Functions for converting between scales (UTC, TAI, TT).

  /**
   * Get the number of leap seconds inserted between when leap seconds started and the given $this
   * datetime.
   *
   * The problem with this function as currently implemented is that it needs updating every time
   * another leap second is inserted.
   *
   * @todo Use a live table such as https://www.ietf.org/timezones/data/leap-seconds.list
   * Cache a local copy of the file and check every 1 January and 1 July for new leap seconds.
   *
   * Another useful link:
   * https://www.nist.gov/pml/time-and-frequency-division/atomic-standards/leap-second-and-ut1-utc-information
   *
   * @return int
   */
  public function leapSeconds(): int {
    // Leap seconds have been inserted at the end of the following months.
    $leap_seconds = [
      [1972, 6],
      [1972, 12],
      [1973, 12],
      [1974, 12],
      [1975, 12],
      [1976, 12],
      [1977, 12],
      [1978, 12],
      [1979, 12],

      [1981, 6],
      [1982, 6],
      [1983, 6],
      [1985, 6],
      [1987, 12],
      [1989, 12],

      [1990, 12],
      [1992, 6],
      [1993, 6],
      [1994, 6],
      [1995, 12],
      [1997, 6],
      [1998, 12],

      [2005, 12],
      [2008, 12],

      [2012, 6],
      [2015, 6],
      [2016, 12],
    ];

    // Create a DateTime object to use for the leap seconds. Actually, since PHP DateTimes are based
    // on Unix timestamps, which do not include leap seconds, we can only create an object for the
    // second before the leap second.
    // Remember, DateTime objects are based on Unix time, not UTC, which means they do not include
    // leap seconds.

    // Create a DateTime object to work with. The date parts don't matter as they will be changed
    // in the loop.
    $ls = new EarthDateTime([1970, 1, 1, 23, 59, 59], 'UTC');

    // Count leap seconds.
    $n_leap_seconds = 0;

    foreach ($leap_seconds as list($year, $month)) {
      // Get the datetime of the second before the leap second.
      $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
      $ls->setDate($year, $month, $days_in_month);

      if ($this > $ls) {
        $n_leap_seconds++;
      }
      else {
        break;
      }
    }

    return $n_leap_seconds;
  }

  /**
   * Get the difference between TAI and UTC, which is equal to the number of leap seconds so far,
   * plus 10, since TAI - UTC was already 10 seconds when leap seconds started.
   *
   * @return int
   */
  public function taiMinusUtc() {
    return 10 + $this->leapSeconds();
  }

  /**
   * Get the difference between TT (Terrestrial Time) and UTC.
   *
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   * and Morrison and Stephenson - Historical Values of the Earth's Clock Error ΔT and the
   * Calculation of Eclipses
   *
   * @return float
   */
  public function ttMinusUtc() {
    $dt1 = new EarthDateTime([1770], 'UTC');
    if ($this < $dt1) {
      // Date is before 1770.
      // Use formula from Morrison and Stephenson.
      $c = (1820 - $this->getYear()) / 100;
      $d = -20 + (32 * pow($c, 2));
    }
    else {
      // Date is 1770 or later.
      $dt2 = new EarthDateTime([1972], 'UTC');
      if ($this < $dt2) {
        // Date is before 1972.
        // Use formula from https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
        $c = ($this->toJulianDate() - 2451545.0) / 36525;
        $d = 64.184 + (59 * $c) - (51.2 * pow($c, 2)) - (67.1 * pow($c, 3)) - (16.4 * pow($c, 4));
      }
      else {
        // Date is 1972 or later.
        // Use leap seconds.
        $d = self::TT_MINUS_TAI + $this->taiMinusUtc();
      }
    }
    return $d;
  }
}
