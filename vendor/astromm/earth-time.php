<?php
/**
 * Created 2017-08-06T16:25:31
 * @author Shaun Moss (shaun@astromultimedia.com)
 *
 * Functions for working with Earth dates and times.
 */

////////////////////////////////////////////////////////////////////////////////
// Functions for converting between DateTime, Modified Julian Date, and
// Julian Date.

/**
 * Returns a Modified Julian Date given a DateTime.
 *
 * @param DateTime $ds
 * @return float
 */
function datetime2mjd(DateTime $dt) {
  $year = $dt->getYear();

  $da = dtlDateStrToArray($ds);
  $year = $da['year'];
  // in the Gregorian calendar there are 146097 days per 400 years
  // which 400 year cycle is $year in?
  $cycle400 = (int) (($year - 1) / 400);
  // a 400 year cycle goes from 1..400, 401..800, 801..1200, 1201..1600, 1601..2000, etc.
  // MJD at end of 2000 = 51909 (cycle400 = 4)
  // MJD at end of previous 400-year cycle:
  $mjd = 51909 + (($cycle400 - 5) * 146097);
  // MJD at end of previous 100-year cycle:
  $year -= $cycle400 * 400;
  $century = (int) (($year - 1) / 100);
  $mjd += $century * 36524;
  // MJD at end of previous 4-year cycle:
  $year -= $century * 100;
  $cycle4 = (int) (($year - 1) / 4);
  $mjd += $cycle4 * 1461;
  // MJD at end of previous year:
  $year -= $cycle4 * 4;
  $mjd += ($year - 1) * 365;
  // MJD at given date:
  // a little trick here - dtlGetDayOfYear will fail if I give it a date in 1582
  // so for dates in 1582 I will give it the same date in 1583 -
  // this works because both are not leap years
  if ($da['year'] == 1582) {
    $ds = dtlDateStr(1583, $da['month'], $da['day']);
  }
  $mjd += dtlGetDayOfYear($ds);
  return $mjd;
}

function dtlMJDToDate($mjd) {
  // * returns a DateStr given an MJD
  // * returns false for invalid input or if $mjd represents an invalid Gregorian date
  //   i.e. < 1582-10-15 or > 9999-12-31
  if (!dtlIsInt($mjd) || $mjd < -100840 || $mjd > 2973483) {
    return FALSE;
  }
  $mjd -= 51909;
  $year = 0;
  $cycle400 = floor(($mjd - 1) / 146097) + 5;
  $year += $cycle400
    * 400; // takes us to the end of the previous 400-year cycle
  $mjd -= ($cycle400 - 5) * 146097;
  $century = (int) (($mjd - 1) / 36524);
  if ($century == 4) {
    $century--;
  }
  $year += $century * 100; // takes us to the end of the previous century
  $mjd -= $century * 36524;
  $cycle4 = (int) (($mjd - 1) / 1461);
  $year += $cycle4 * 4; // takes us to the end of the previous 4-year cycle
  $mjd -= $cycle4 * 1461;
  $yr = (int) (($mjd - 1) / 365);
  if ($yr == 4) {
    $yr--;
  }
  $year += $yr + 1; // takes us to the desired year
  $mjd -= $yr * 365;  // $mjd is now the day of $year
  return dtlDateFromDayOfYear($year, $mjd);
}

function dtlDateTimeToMJD($dts) {
  // * returns a Modified Julian Date given a valid DateTimeStr
  // * returns false for invalid input
  if (!dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  $dta = dtlDateTimeStrToArray($dts);
  // get the whole MJD:
  $mjd = dtlDateToMJD($dta['date']);
  // calculate fractional part:
  $frac = ($dta['hours'] / 24) + ($dta['minutes'] / 1440) + ($dta['seconds']
      / 86400);
  $mjd += $frac;
  return $mjd;
}

function dtlMJDToDateTime($mjd) {
  // * returns a DateTimeStr given an MJD
  // * returns false for invalid input
  // convert to a float:
  $mjd = (float) $mjd;
  // break into whole number of days and fraction of a day:
  $day = floor($mjd);
  $frac = $mjd - $day;
  // find the date:
  $ds = dtlMJDToDate($day);
  // find the time:
  $frac *= 24;
  $hours = floor($frac);
  $frac = ($frac - $hours) * 60;
  $minutes = floor($frac);
  $frac = ($frac - $minutes) * 60;
  $seconds = round($frac);
  $ts = dtlTimeStr($hours, $minutes, $seconds);
  // assemble the datestring and return:
  return "$ds $ts";
}

function dtlMJDToJD($mjd) {
  // * returns a Modified Julian Date given a Julian Date
  // * returns false for invalid input
  if (!dtlIsInt($mjd)) {
    return FALSE;
  }
  return $mjd + 2400000.5;
}

function dtlJDToMJD($jd) {
  // * returns a Julian Date given a Modified Julian Date
  // * returns false for invalid input
  if (!dtlIsInt($jd)) {
    return FALSE;
  }
  return $jd - 2400000.5;
}

function dtlDateStrToJD($ds) {
  // * returns a Julian Date given a DateStr
  // * return false for non-Gregorian dates
  return dtlMJDToJD(dtlDateToMJD($ds));
}

function dtlJDToDateStr($jd) {
  // * returns a DateStr given a Julian Date
  // * return false for non-Gregorian dates
  return dtlMJDToDate(dtlJDToMJD($jd));
}
