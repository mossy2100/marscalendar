<?php

/**
 * This class extends and improve PHP's built-in DateTime class.
 */
class StarDateTime extends DateTime {

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

  //////////////////////////////////////////////////////////////////////////////////////////////////
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

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Constructor

  /**
   * Constructor for making dates and datetimes.
   * Time zones may be provided as DateTimeZone objects, or as timezone strings.
   *
   * @todo Add support for microseconds (PHP 7.1 and above).
   *
   * Support usage:
   *    $dt = new StarDateTime();
   *    $dt = new StarDateTime($dt_str);
   *    $dt = new StarDateTime($dt_str, $tz);
   *    $dt = new StarDateTime($y, $mon, $d);
   *    $dt = new StarDateTime($y, $mon, $d, $tz);
   *    $dt = new StarDateTime($y, $mon, $d, $h, $min, $s);
   *    $dt = new StarDateTime($y, $mon, $d, $h, $min, $s, $tz);
   *
   * @see http://php.net/manual/en/datetime.construct.php
   *
   * @param string|$dt_str or int $y
   * @param DateTimeZone|string $tz or int $mon
   * @param int $d
   * @param DateTimeZone|string $tz or int $h
   * @param int $min
   * @param int $s
   * @param DateTimeZone|string $tz
   */
  public function __construct() {
    // All arguments are optional and several serve multiple roles, so it's simpler not to show
    // parameters in the function signature, and instead just grab them as follows.
    $n_args = func_num_args();
    $args = func_get_args();

    // Initialise.
    $dt_str = NULL;
    $tz = NULL;

    if ($n_args <= 2) {
      // Args are assumed to be [$dt_str, [$tz]]
      // i.e. the same as the DateTime constructor.
      $dt_str = (!isset($args[0]) || $args[0] === '') ? 'now' : $args[0];
      $tz = isset($args[1]) ? $args[1] : NULL;
    }
    elseif ($n_args >= 3 && $n_args <= 4) {
      // Args are assumed to be $y, $mon, $d, [$tz]
      $y = self::padDigits((int) $args[0], 4);
      $mon = self::padDigits((int) $args[1]);
      $d = self::padDigits((int) $args[2]);
      $dt_str = "{$y}-{$mon}-{$d}T00:00:00";
      $tz = isset($args[3]) ? $args[3] : NULL;
    }
    elseif ($n_args >= 6 && $n_args <= 7) {
      // Args are assumed to be $y, $mon, $d, $h, $min, $s, [$tz]
      $y = self::padDigits((int) $args[0], 4);
      $mon = self::padDigits((int) $args[1]);
      $d = self::padDigits((int) $args[2]);
      $h = self::padDigits((int) $args[3]);
      $min = self::padDigits((int) $args[4]);
      $s = self::padDigits((int) $args[5]);
      $dt_str = "{$y}-{$mon}-{$d}T{$h}:{$min}:{$s}";
      $tz = isset($args[6]) ? $args[6] : NULL;
    }
    else {
      trigger_error("Invalid number of arguments.");
    }

    // Support timezones as strings.
    if (is_string($tz)) {
      $tz = new DateTimeZone($tz);
    }
    // Check we have a valid timezone.
    if (!is_null($tz) && !($tz instanceof DateTimeZone)) {
      trigger_error("Invalid timezone.");
    }

    // Call parent constructor:
    parent::__construct($dt_str, $tz);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Helper functions.

  /**
   * Pads a number with '0' characters up to a specified width.
   * Probably belongs in a function library.
   *
   * @param int $n
   * @param int $w
   *
   * @return string
   */
  public static function padDigits($n, $w = 2) {
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

  //////////////////////////////////////////////////////////////////////////////////////////////////
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
   *
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
   * @param int $microsecond
   *
   * @return self
   */
  public function setTime($hour, $minute = 0, $second = 0, $microsecond = 0) {
    if (php_version_at_least(7.1)) {
      parent::setTime($hour, $minute, $second, $microsecond);
    }
    else {
      if ($microsecond != 0) {
        $second = round($second + ($microsecond / 1e6));
      }
      parent::setTime($hour, $minute, $second);
    }
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
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
   *
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
   *
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
   *
   * @return self
   */
  public function setDay($day) {
    return $this->setDate($this->getYear(), $this->getMonth(), $day);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
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
   *
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
   *
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
   *
   * @return self
   */
  public function setSecond($second) {
    return $this->setTime($this->getHour(), $this->getMinute(), $second);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Additional handy getters and setters.

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

  /**
   * Set the timezone. Unlike the parent method, accepts strings.
   *
   * @param DateTimeZone|string
   *
   * @return self
   */
  public function setTimezone($timezone) {
    if (is_string($timezone)) {
      $timezone = new DateTimeZone($timezone);
    }

    // Check the timezone is valid.
    if (!is_null($timezone) && !($timezone instanceof DateTimeZone)) {
      trigger_error(E_USER_WARNING, "Invalid timezone.");
    }

    parent::setTimezone($timezone);
    return $this;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Methods for adding and subtracting durations.
  // These methods return a new StarDateTime object; they don't modify the calling object.
  //
  // @todo Test all methods in this section.

  /**
   * Constructs a DateInterval object.
   *
   * @param int $years
   * @param int $months
   * @param int $days
   * @param int $hours
   * @param int $minutes
   * @param int $seconds
   *
   * @return DateInterval
   */
  public static function interval($years, $months = 0, $days = 0, $hours = 0, $minutes = 0,
      $seconds = 0) {
    $interval = new DateInterval('P');
    $interval->y = $years;
    $interval->m = $months;
    $interval->d = $days;
    $interval->h = $hours;
    $interval->i = $minutes;
    $interval->s = $seconds;
    return $interval;
  }

  /**
   * Add a period of time.
   *
   * @param int $years
   * @param int $months
   * @param int $days
   * @param int $hours
   * @param int $minutes
   * @param int $seconds
   *
   * @return self
   */
  public function addTime($years, $months = 0, $days = 0, $hours = 0, $minutes = 0, $seconds = 0) {
    $interval = StarDateTime::interval($years, $months, $days, $hours, $minutes, $seconds);
    $dt = clone $this;
    return $dt->add($interval);
  }

  /**
   * Subtract a period of time.
   *
   * @param int $years
   * @param int $months
   * @param int $days
   * @param int $hours
   * @param int $minutes
   * @param int $seconds
   *
   * @return self
   */
  public function subTime($years, $months = 0, $days = 0, $hours = 0, $minutes = 0, $seconds = 0) {
    $interval = StarDateTime::interval($years, $months, $days, $hours, $minutes, $seconds);
    $dt = clone $this;
    return $dt->sub($interval);
  }

  /**
   * Add years.
   *
   * @param int $years
   *
   * @return self
   */
  public function addYears($years) {
    return $this->addTime($years);
  }

  /**
   * Subtract years.
   *
   * @param int $years
   *
   * @return self
   */
  public function subYears($years) {
    return $this->addYears(-$years);
  }

  /**
   * Add months.
   *
   * @param int $months
   *
   * @return self
   */
  public function addMonths($months) {
    return $this->addTime(0, $months);
  }

  /**
   * Subtract months.
   *
   * @param int $months
   *
   * @return self
   */
  public function subMonths($months) {
    return $this->addMonths(-$months);
  }

  /**
   * Add weeks.
   *
   * @param int $weeks
   *
   * @return self
   */
  public function addWeeks($weeks) {
    return $this->addDays($weeks * 7);
  }

  /**
   * Subtract weeks.
   *
   * @param int $weeks
   *
   * @return self
   */
  public function subWeeks($weeks) {
    return $this->addWeeks(-$weeks);
  }

  /**
   * Add days.
   *
   * @param int $days
   *
   * @return self
   */
  public function addDays($days) {
    return $this->addTime(0, 0, $days);
  }

  /**
   * Subtract days.
   *
   * @param int $days
   *
   * @return self
   */
  public function subDays($days) {
    return $this->addDays(-$days);
  }

  /**
   * Add hours.
   *
   * @param int $hours
   *
   * @return self
   */
  public function addHours($hours) {
    return $this->addTime(0, 0, 0, $hours);
  }

  /**
   * Subtract hours.
   *
   * @param int $hours
   *
   * @return self
   */
  public function subHours($hours) {
    return $this->addHours(-$hours);
  }

  /**
   * Add minutes.
   *
   * @param int $minutes
   *
   * @return self
   */
  public function addMinutes($minutes) {
    return $this->addTime(0, 0, 0, 0, $minutes);
  }

  /**
   * Subtract minutes.
   *
   * @param int $minutes
   *
   * @return self
   */
  public function subMinutes($minutes) {
    return $this->addMinutes(-$minutes);
  }

  /**
   * Add seconds.
   *
   * @param int $seconds
   *
   * @return self
   */
  public function addSeconds($seconds) {
    return $this->addTime(0, 0, 0, 0, 0, $seconds);
  }

  /**
   * Subtract seconds.
   *
   * @param int $seconds
   *
   * @return self
   */
  public function subSeconds($seconds) {
    return $this->addSeconds(-$seconds);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
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
   * Generates a string describing the difference in time between this StarDateTime object and
   * another.
   *
   * @param self $dt
   *
   * @return string
   */
  public function diffApproxText(self $dt = null) {
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
   * @param self $dt2
   * @param bool $absolute
   *   If TRUE then the absolute value of the difference is returned.
   *
   * @return int
   */
  public function diffSeconds(self $datetime2, $absolute = FALSE) {
    $diff = $this->getTimestamp() - $datetime2->getTimestamp();
    if ($absolute) {
      $diff = abs($diff);
    }
    return $diff;
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion functions between Gregorian and Julian dates.

  /**
   * Construct a new StarDateTime from a Julian Date (UTC).
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
   * @return self
   */
  public static function fromJulianDate(float $jdate) {
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
    $second = round($seconds);

    // Create a result object.
    $result = new StarDateTime('2000-01-01T00:00:00.0', 'UTC');
    $result->setYear($year);
    $result->setMonth($month);
    $result->setDay($day);
    $result->setHour($hour);
    $result->setMinute($minute);
    $result->setSecond($second);
    return $result;
  }

  /**
   * Convert a Julian Date to a StarDateTime via a Unix timestamp.
   * (Ignores issues with the limited range of the Unix timestamp on 32-bit systems.)
   *
   * @param float $jd
   *
   * @return self
   */
  public static function fromJulianDate2(float $jd) {
    $dt = new StarDateTime('now', 'UTC');
    $t = ($jd - self::JD_MINUS_UNIX) * self::SECONDS_PER_DAY;
    $dt->setTimestamp($t);
    return $dt;
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
   *
   * @return float
   */
  public function toJulianDate($scale = 'UTC') {
    $t = $this->getTimestamp();
    $d = 0;

    switch ($scale) {
      case 'UTC':
        break;

      case 'TAI':
        $d = $this->taiMinusUtc();
        break;

      case 'TT':
        $d = $this->ttMinusUtc();
        break;

      default:
        trigger_error("Scale must be 'UTC', 'TAI', or 'TT', or omit for UTC.");
        break;
    }

    return ($t + $d) / self::SECONDS_PER_DAY + self::JD_MINUS_UNIX;
  }

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
  public function leapSeconds() {
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

    // Create a DateTime object to work with. The date fields don't matter as they will be changed
    // in the loop.
    $ls = new StarDateTime(2000, 1, 1, 23, 59, 59, 'UTC');

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
   * Formulae from:
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   * and Morrison and Stephenson - Historical Values of the Earth's Clock Error ΔT and the
   * Calculation of Eclipses
   *
   * @return float
   */
  public function ttMinusUtc() {
    $dt1 = new StarDateTime(1770, 1, 1, 'UTC');
    $dt2 = new StarDateTime(1972, 1, 1, 'UTC');
    if ($this < $dt1) {
      $c = (1820 - $this->getYear()) / 100;
      $d = -20 + (32 * pow($c, 2));
    }
    elseif ($this < $dt2) {
      $c = ($this->toJulianDate() - 2451545.0) / 36525;
      $d = 64.184 + (59 * $c) - (51.2 * pow($c, 2)) - (67.1 * pow($c, 3)) - (16.4 * pow($c, 4));
    }
    else {
      $d = self::TT_MINUS_TAI + $this->taiMinusUtc();
    }
    return $d;
  }
}
