<?php

/**
 * This class extends and improve PHP's built-in DateTime class.
 */
class StarDateTime extends DateTime {

  //////////////////////////////////////////////////////////////////////////////
  // Constants

  // These values are based on average Gregorian calendar month and year lengths.
  const SECONDS_PER_MINUTE  = 60;
  const SECONDS_PER_HOUR    = 3600;
  const SECONDS_PER_DAY     = 86400;
  const SECONDS_PER_WEEK    = 604800;
  const SECONDS_PER_MONTH   = 2629746;
  const SECONDS_PER_YEAR    = 31556952;

  const MINUTES_PER_HOUR    = 60;
  const MINUTES_PER_DAY     = 1440;
  const MINUTES_PER_WEEK    = 10080;
  const MINUTES_PER_MONTH   = 43829.1;
  const MINUTES_PER_YEAR    = 525949.2;

  const HOURS_PER_DAY       = 24;
  const HOURS_PER_WEEK      = 168;
  const HOURS_PER_MONTH     = 730.485;
  const HOURS_PER_YEAR      = 8765.82;

  const DAYS_PER_WEEK       = 7;
  const DAYS_PER_MONTH      = 30.436875;
  const DAYS_PER_YEAR       = 365.2425;

  const WEEKS_PER_MONTH     = 4.348125;
  const WEEKS_PER_YEAR      = 52.1775;

  const MONTHS_PER_YEAR     = 12;

  //////////////////////////////////////////////////////////////////////////////
  // Static methods

  /**
   * The current datetime as an StarDateTime object.
   *
   * @return self
   */
  public static function now() {
    // This will call the parent constructor, which defaults to 'now'.
    return new self();
  }

  /**
   * Today's date as an StarDateTime object.
   *
   * @return self
   */
  public static function today() {
    $now = self::now();
    return $now->getDate();
  }

  //////////////////////////////////////////////////////////////////////////////
  // Constructor

  /**
   * Constructor for making dates and datetimes.
   * Time zones may be provided as DateTimeZone objects, or as timezone strings.
   * All arguments are optional.
   *
   * Usage examples:
   *    $dt = new StarDateTime();
   *    $dt = new StarDateTime($unix_timestamp);
   *    $dt = new StarDateTime($datetime_string);
   *    $dt = new StarDateTime($datetime_string, $timezone);
   *    $dt = new StarDateTime($year, $month, $day);
   *    $dt = new StarDateTime($year, $month, $day, $timezone);
   *    $dt = new StarDateTime($year, $month, $day, $hour, $minute, $second);
   *    $dt = new StarDateTime($year, $month, $day, $hour, $minute, $second, $timezone);
   *
   * @param string|int $year, $unix_timestamp or $datetime_string
   * @param null|DateTimeZone|string|int $month or $timezone
   * @param int $day
   * @param null|DateTimeZone|string|int $hour or $timezone
   * @param int $minute
   * @param int $second
   * @param null|DateTimeZone|string $timezone
   */
  public function __construct() {
    // All arguments are optional and several serve multiple roles, so it's
    // simpler not to include parameters in the function signature, and instead
    // just grab them as follows.
    $n_args = func_num_args();
    $args = func_get_args();

    // Initialise.
    $timezone = NULL;
    $datetime = NULL;

    if ($n_args == 0) {
      // Now:
      $datetime = 'now';
    }
    elseif ($n_args == 1 && is_numeric($args[0])) {
      // Unix timestamp:
      $datetime = '@' . $args[0];
    }
    elseif ($n_args <= 2) {
      // Args are assumed to be: $datetime, [$timezone], as for the DateTime
      // constructor.
      $datetime = $args[0];
      $timezone = isset($args[1]) ? $args[1] : NULL;
    }
    elseif ($n_args <= 4) {
      // Args are assumed to be: $year, $month, $day, [$timezone].
      $date = self::padDigits($args[0], 4) . '-' . self::padDigits($args[1]) . '-'
        . self::padDigits($args[2]);
      $time = '00:00:00';
      $datetime = "$date $time";
      $timezone = isset($args[3]) ? $args[3] : NULL;
    }
    elseif ($n_args >= 6 && $n_args <= 7) {
      // Args are assumed to be: $year, $month, $day, [$timezone].
      $date = self::padDigits($args[0], 4) . '-' . self::padDigits($args[1]) . '-'
        . self::padDigits($args[2]);
      $time = self::padDigits($args[3]) . ':' . self::padDigits($args[4]) . ':' .
        self::padDigits($args[5]);
      $datetime = "$date $time";
      $timezone = isset($args[6]) ? $args[6] : NULL;
    }
    else {
      trigger_error(E_USER_WARNING, "Invalid number of arguments to constructor.");
    }

    // Support string timezones:
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    // Check we have a valid timezone:
    if ($timezone !== NULL && !($timezone instanceof DateTimeZone)) {
      trigger_error(E_USER_WARNING, "Invalid timezone provided to constructor.");
    }

    // Call parent constructor:
    parent::__construct($datetime, $timezone);
  }

  /**
   * Pads a number with '0' characters up to a specified width.
   *
   * @param int $n
   * @param int $w
   * @return string
   */
  protected static function padDigits($n, $w = 2) {
    return str_pad((int) $n, $w, '0', STR_PAD_LEFT);
  }

  /**
   * Convert the datetime to a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->format('Y-m-d H:i:s P e');
  }

  //////////////////////////////////////////////////////////////////////////////
  // Getters/setters for date and time.

  /**
   * Get the date.
   *
   * @return self
   */
  public function getDate() {
    return new self($this->format('Y-m-d'));
  }

  /**
   * Set the date.
   *
   * @param int $year
   * @param int $month
   * @param int $day
   * @return self
   */
  public function setDate($year, $month = 1, $day = 1) {
    // Set the date:
    parent::setDate($year, $month, $day);
    return $this;
  }

  /**
   * Get the time.
   *
   * @return DateInterval
   */
  public function getTime() {
    return new DateInterval('PT' . $this->format('His'));
  }

  /**
   * Set the time.
   *
   * @param int $hour
   * @param int $minute
   * @param int $second
   * @return self
   */
  public function setTime($hour, $minute = 0, $second = 0) {
    parent::setTime($hour, $minute, $second);
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////
  // Getters/setters for date parts.

  /**
   * Get the year.
   *
   * @return int
   */
  public function getYear() {
    return (int) $this->format('Y');
  }

  /**
   * Set the year.
   *
   * @param int $year
   * @return self
   */
  public function setYear($year) {
    return $this->setDate($year, $this->getMonth(), $this->getDay());
  }

  /**
   * Get the month.
   *
   * @return int
   */
  public function getMonth() {
    return (int) $this->format('n');
  }

  /**
   * Set the month.
   *
   * @param int $month
   * @return self
   */
  public function setMonth($month) {
    return $this->setDate($this->getYear(), $month, $this->getDay());
  }

  /**
   * Get the day of the month.
   *
   * @return int
   */
  public function getDay() {
    return (int) $this->format('j');
  }

  /**
   * Set the day of the month.
   *
   * @param int $day
   * @return self
   */
  public function setDay($day) {
    return $this->setDate($this->getYear(), $this->getMonth(), $day);
  }

  //////////////////////////////////////////////////////////////////////////////
  // Getters/setters for time parts.

  /**
   * Get the hour.
   *
   * @return int
   */
  public function getHour() {
    return (int) $this->format('G');
  }

  /**
   * Set the hour.
   *
   * @param int $hour
   * @return self
   */
  public function setHour($hour) {
    return $this->setTime($hour, $this->getMinute(), $this->getSecond());
  }

  /**
   * Get the minute.
   *
   * @return int
   */
  public function getMinute() {
    return (int) $this->format('i');
  }

  /**
   * Set the minute.
   *
   * @param int $minute
   * @return self
   */
  public function setMinute($minute) {
    return $this->setTime($this->getHour(), $minute, $this->getSecond());
  }

  /**
   * Get the second.
   *
   * @return int
   */
  public function getSecond() {
    return (int) $this->format('s');
  }

  /**
   * Set the second.
   *
   * @param int $second
   * @return self
   */
  public function setSecond($second) {
    return $this->setTime($this->getHour(), $this->getMinute(), $second);
  }

  //////////////////////////////////////////////////////////////////////////////
  // Additional handy getters.

  /**
   * Get the week of the year as an integer (1.. 52).
   *
   * @return int
   */
  public function getWeek() {
    return (int) $this->format('W');
  }

  /**
   * Get the day of the year as an integer (1..366).
   *
   * @return int
   */
  public function getDayOfYear() {
    return ((int) $this->format('z')) + 1;
  }

  /**
   * Get the day of the week as an integer (1..7).
   * 1 = Monday .. 7 = Sunday
   *
   * @return int
   */
  public function getDayOfWeek() {
    return (int) $this->format('N');
  }

  //////////////////////////////////////////////////////////////////////////////
  // Add periods. These methods return a new StarDateTime object; they don't
  // modify the calling object.

  /**
   * Add years.
   *
   * @param int $years
   * @return self
   */
  public function addYears($years) {
    $dt = clone $this;
    return $dt->setYear($dt->getYear() + $years);
  }

  /**
   * Add months.
   *
   * @param int $months
   * @return self
   */
  public function addMonths($months) {
    $dt = clone $this;
    return $dt->setMonth($dt->getMonth() + $months);
  }

  /**
   * Add weeks.
   *
   * @param int $weeks
   * @return self
   */
  public function addWeeks($weeks) {
    return $this->addDays($weeks * 7);
  }

  /**
   * Add days.
   *
   * @param int $days
   * @return self
   */
  public function addDays($days) {
    $dt = clone $this;
    return $dt->setDay($dt->getDay() + $days);
  }

  /**
   * Add hours.
   *
   * @param int $hours
   * @return self
   */
  public function addHours($hours) {
    $dt = clone $this;
    return $dt->setHour($dt->getHour() + $hours);
  }

  /**
   * Add minutes.
   *
   * @param int $minutes
   * @return self
   */
  public function addMinutes($minutes) {
    $dt = clone $this;
    return $dt->setMinute($dt->getMinute() + $minutes);
  }

  /**
   * Add seconds.
   *
   * @param int $seconds
   * @return self
   */
  public function addSeconds($seconds) {
    $dt = clone $this;
    return $dt->setSecond($dt->getSecond() + $seconds);
  }

  //////////////////////////////////////////////////////////////////////////////
  // Subtract periods. These methods return a new StarDateTime object; they
  // don't modify the calling object.

  /**
   * Subtract years.
   *
   * @param int $years
   * @return self
   */
  public function subYears($years) {
    return $this->addYears(-$years);
  }

  /**
   * Subtract months.
   *
   * @param int $months
   * @return self
   */
  public function subMonths($months) {
    return $this->addMonths(-$months);
  }

  /**
   * Subtract weeks.
   *
   * @param int $weeks
   * @return self
   */
  public function subWeeks($weeks) {
    return $this->addWeeks(-$weeks);
  }

  /**
   * Subtract days.
   *
   * @param int $days
   * @return self
   */
  public function subDays($days) {
    return $this->addDays(-$days);
  }

  /**
   * Subtract hours.
   *
   * @param int $hours
   * @return self
   */
  public function subHours($hours) {
    return $this->addHours(-$hours);
  }

  /**
   * Subtract minutes.
   *
   * @param int $minutes
   * @return self
   */
  public function subMinutes($minutes) {
    return $this->addMinutes(-$minutes);
  }

  /**
   * Subtract seconds.
   *
   * @param int $seconds
   * @return self
   */
  public function subSeconds($seconds) {
    return $this->addSeconds(-$seconds);
  }

  //////////////////////////////////////////////////////////////////////////////
  // Miscellaneous useful functions.

  /**
   * Clamp the year to a specified range.
   * Either min or max or neither or both can be specified.
   *
   * @param int|null $min_year
   * @param int|null $max_year
   */
  public function clampYear($min_year = NULL, $max_year = NULL) {
    $year = $this->getYear();
    // Clamp to min year, if specified:
    if ($min_year !== NULL && $year < $min_year) {
      $this->setYear($min_year);
    }
    // Clamp to max year, if specified:
    if ($max_year !== NULL && $year > $max_year) {
      $this->setYear($max_year);
    }
  }

  /**
   * Generates a string describing how long ago a datetime was.
   *
   * @return string
   */
  public function aboutHowLongAgo() {
    $ts = $this->getTimestamp();
    $now = time();

    // Get the time difference in seconds:
    $seconds = $now - $ts;

    // Check time is in the past:
    if ($seconds < 0) {
      trigger_error("StarDateTime::aboutHowLongAgo() only works with datetimes in the past.", E_USER_WARNING);
      return FALSE;
    }

    // Now:
    if ($seconds == 0) {
      return 'now';
    }

    // Seconds:
    if ($seconds <= 20) {
      return $seconds == 1 ? 'a second' : "$seconds seconds";
    }

    // 5 seconds:
    if ($seconds < 58) {
      return (round($seconds / 5) * 5) . ' seconds';
    }

    // Minutes:
    $minutes = round($seconds / self::SECONDS_PER_MINUTE);
    if ($minutes <= 20) {
      return $minutes == 1 ? 'a minute' : "$minutes minutes";
    }

    // 5 minutes:
    if ($minutes < 58) {
      return (round($minutes / 5) * 5) . ' minutes';
    }

    // Hours:
    $hours = round($seconds / self::SECONDS_PER_HOUR);
    if ($hours < 48 && $hours % self::HOURS_PER_DAY != 0) {
      return $hours == 1 ? 'an hour' : "$hours hours";
    }

    // Days:
    $days = round($seconds / self::SECONDS_PER_DAY);
    if ($days < 28 && $days % self::DAYS_PER_WEEK != 0) {
      return $days == 1 ? 'a day' : "$days days";
    }

    // Weeks:
    $weeks = round($seconds / self::SECONDS_PER_WEEK);
    if ($weeks <= 12) {
      return $weeks == 1 ? 'a week' : "$weeks weeks";
    }

    // Months:
    $months = round($seconds / self::SECONDS_PER_MONTH);
    if ($months < 24 && $months % self::MONTHS_PER_YEAR != 0) {
      return $months == 1 ? 'a month' : "$months months";
    }

    // Years:
    $years = round($seconds / self::SECONDS_PER_YEAR);
    return $years == 1 ? 'a year' : "$years years";
  }

  /**
   * Calculate the difference in seconds between two datetimes.
   *
   * The signature is identical to DateTime::diff(), except that it returns the
   * difference in seconds rather than as a DateInterval.
   *
   * @param self $dt2
   * @param bool $absolute
   *   If TRUE then the absolute value of the difference is returned.
   * @return int
   */
  function diffSeconds(self $datetime2, $absolute = FALSE) {
    $diff = $this->getTimestamp() - $datetime2->getTimestamp();
    if ($absolute) {
      $diff = abs($diff);
    }
    return $diff;
  }

  //////////////////////////////////////////////////////////////////////////////
  // Conversion functions.

  /**
   * Get the datetime as a Julian Date.
   *
   * @return float
   */
  function getJulianDate() {
    return unixtojd($this->getTimestamp());
  }

}
