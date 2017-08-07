<?php
/**
 * Created by PhpStorm at 2017-08-06T15:50
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

/**
Functions for calculating the epoch start datetime.
 */

/**
 * Number of SI seconds in a Martian solar day (sol).
 * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
 */
const SECONDS_PER_SOL = 88775.244147;

/**
 * Number of Terran days in a sol.
 * @see https://www.wikiwand.com/en/Timekeeping_on_Mars
 */
const SOLS_PER_DAY = 1.02749125170;

/**
 * Calculate the MST (Mean Solar Time) on Mars for a given Terrestrial Time.
 *
 * @see Allison, 1997 - Accurate analytic representations of solar time and
 * seasons on Mars with applications to the Pathfinder/Surveyor missions
 *
 * Using the decimal clock instead of the stretched clock, the equation given
 * by Allison can be re-written as follows to give the time at the primar
 * meridian as follows:
 *
 * MST = frac[(JD - 2440692) / 1.02749125] * 1000 mils/sol
 *
 * @param float $JD
 *   The Julian Day count
 * @return float
 *   The time of the day in mils.
 */
function mars_mean_solar_time(float $JD) {
  $t = ($JD - 2440692) / SOLS_PER_DAY;
  $f = $t - floor($t);
  return $f * 1000;
}

/**
 * Calculate the Mars Sol Date (MSD), which is analogous to the Julian Day
 * count for Mars.
 * It's a running count of sols since December 29, 1873.
 *
 * @param float $JDTT
 * @return float
 */
function mars_sol_date(float $JDTT) {
  return ($JDTT - 2405522.0028779) / SOLS_PER_DAY;

//  MSD = (t + (TAI−UTC)) / SECONDS_PER_SOL + 34127.2954262

}
