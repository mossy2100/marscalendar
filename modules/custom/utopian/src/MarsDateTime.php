<?php
/**
 * Created by PhpStorm at 2017-08-06T15:50
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 *
 * Functions for calculating the epoch start datetime.
 */

require_once "StarDateTime.php";

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
   * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
   */
  const SECONDS_PER_SOL = 88775.244147;

  /**
   * Number of Terran days in a sol.
   * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
   */
  const DAYS_PER_SOL = 1.0274912517;

  /**
   * Tropical mir length, based on northern vernal equinox,
   * i.e. average number of sols between northern vernal equinoxes.
   */
  const SOLS_PER_MIR = 668.5907;

  /**
   * Number of days in a mir.
   */
  const DAYS_PER_MIR = self::DAYS_PER_SOL * self::SOLS_PER_MIR;

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Properties

  /**
   * Internal representation of the datetime as a StarDateTime object.
   * StarDateTime is an extension of the built-in PHP DateTime class.
   *
   * @var StarDateTime
   */
  protected $datetime;

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Conversion methods

  /**
   * Construct a new MarsDateTime from a Julian Date.
   *
   * @param float $jdate
   *
   * @return self
   */
  public static function fromJulianDate(float $jdate) {
    $mdt = new MarsDateTime();
    $mdt->datetime = StarDateTime::fromJulianDate2($jdate);
    return $mdt;
  }

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
  public function toJulianDate($scale = 'UTC') {
    return $this->datetime->toJulianDate($scale);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Getters

  /**
   * Get the StarDateTime.
   *
   * @return StarDateTime.
   */
  public function getDateTime() {
    return $this->datetime;
  }

  /**
   * Calculate the Mars Sol Date (MSD), which is analogous to the Julian Day count for Mars.
   * It's a running count of sols since December 29, 1873.
   *
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   *
   * @return float
   */
  public function getMarsSolDate() {
    $jdtt = $this->toJulianDate('TT');
    echo "JDTT = $jdtt\n";
    return ($jdtt - 2405522.0028779) / self::DAYS_PER_SOL;
  }

  /**
   * Calculate MTC (Coordinated Mars Time), i.e. the local time of day at 0° longitude.
   * Result is a fraction of a sol (i.e. 0.0 = midnight, 0.5 = noon).
   *
   * @see https://www.giss.nasa.gov/tools/mars24/help/algorithm.html
   *
   * @return float
   */
  public function getMTC() {
    $msd = $this->getMarsSolDate();
    return $msd - floor($msd);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Static methods

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
  public static function northernVernalEquinox($mir) {
//    $jd = 686.971033153 * $mir + 2308805.796363; // earlier calc
    $jd = 686.971032958 * $mir + 2308806.29606;
    return MarsDateTime::fromJulianDate($jd);
  }
}
