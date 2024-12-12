<?php
// dtl.php

// International Date/Time Library - PHP version
// by Shaun Moss

/*
This library was developed to provide a standard set of Gregorian/UTC date and time functions
for web programmers, whether programming in Javascript, JScript, Actionscript, VBScript or PHP.

The reason for this library is that no two languages or databases support times and dates in the same way.  There are many variations in storage, range, terminology, and formats.

In web programming I'm almost always working with dates in string format, whether reading them from html form fields, writing them into a database, or displaying them on a web page.  So I decided to make a date/time library which used dates and times in string format.

To distinguish between different variables, I use the following prefixes (Hungarian notation).  I don't always use Hungarian notation but when working with dates and times it's very handy.

Objects:
t = time (as a string)
d = date (as a string)
dt = datetime (as a string)

ta = time array
da = date array
dta = datetime array

fds = formatted date string (e.g. 4-Jan-2003)
fts = formatted time string (e.g. 3:15pm)
fdts = formated datetime string (e.g. 3:15pm Saturday, 4th January 2003)


In this version, dates, times, and datetimes are represented by strings.
1. a DateStr is a string in the format YYYY-MM-DD
2. a TimeStr is a string in the format HH:mm:ss
3. a DateTimeStr is a string in the format YYYY-MM-DD HH:mm:ss
These formats can be used in SQL statements for MySQL and ODBC databases.
If you use these formats instead of local formats (e.g. MM/DD/YY or DD-MM-YY) in database applications, they will be more portable and immune to Y2K issues.

Objects used to represent datetimes as supported by the different scripting languages:

Terminology
~~~~~~~~~~~
The word 'day' is used in different languages to mean different things - it can mean the day of the month, the day of week as a number, or the day of the week as a string.
In this library, it refers to the day of the month.
Other abbreviations used are as follows.
dow = day of week
doy = day of year
wom = week of month
woy = week of year


Unix Timestamp as used in PHP:
Zeropoint: Unix Epoch (1-Jan-1970)
Precision: seconds
Range: approximately Fri, 13-Dec-1901 20:45:54 UT to Tue, 19-Jan-2038 03:14:07 UT
2-digit years: 00..69 -> 2000..2069, 70..99 -> 1970..1999
prefix: uts

JScript Date object:
Zeropoint: Unix Epoch (1-Jan-1970)
Precision: milliseconds
Range: approximately 1-Jan-1970 +/- 285,616 years.
2-digit years: 00.99 -> 1900..1999
prefix: jsd

VBScript Date variable:
Zeropoint:
Range: 100AD to 10000AD
2-digit years: 00..29 -> 2000..2029, 30..99 -> 1930..1999
prefix: vbsd

months: 1..12
day of week: 1..7
day of month: 1..31
day of year: 1..366
*/

///////////////////////////////////////////////////////////////////////////
// constants:
$dtlDaysPerTropicalYear = 365.242199;
$dtlDaysPerCalendarYear = 365.2425;
$dtlDaysPerLunarMonth = 29.53;

///////////////////////////////////////////////////////////////////////////
// support functions

function dtlZeroPad($n, $nDigits = 2) {
  // * pads $n with zeroes on the left-hand-side
  // * return a string at least $nDigits long (default = 2)
  return str_pad($n, $nDigits, '0', STR_PAD_LEFT);
}

function dtlAbbrev($str, $nChars = 3) {
  // * returns the first $nChars letters of $str (default = 3)
  // * supports HTML special character codes (required for languages like Russian)
  $result = '';
  $p = 0; // position in $str
  $strlen = strlen($str);
  $length = 0; // number of chars - a HTML char code such as &lt; is counted as one char
  while ($length < $nChars && $p < $strlen) {
    // get the next char from $str and append to $result:
    $ch = $str{$p++};
    $result .= $ch;
    if ($ch == '&') {
      // keep copying chars until ; found
      while ($ch != ';') {
        $ch = $str{$p++};
        $result .= $ch;
      }
    }
    $length++;
  }
  return $result;
}

function dtlOrdinalSuffix($n, $attachNumber = TRUE) {
  // * returns ordinal suffix of $n: {st, nd, rd, th}
  // * returns false if $n is not a positive integer
  // * if $attachNumber true then the number is included
  if (!dtlIsInt($n) || $n <= 0) {
    return FALSE;
  }
  if ($n % 10 == 1 && $n % 100 != 11) {
    $suffix = 'st';
  }
  else {
    if ($n % 10 == 2 && $n % 100 != 12) {
      $suffix = 'nd';
    }
    else {
      if ($n % 10 == 3 && $n % 100 != 13) {
        $suffix = 'rd';
      }
      else {
        $suffix = 'th';
      }
    }
  }
  if ($attachNumber) {
    $suffix = $n . $suffix;
  }
  return $suffix;
}

function dtlIsInt($n) {
  // * returns true if $n is an integer or a string that looks like an integer:
  return is_int($n) || (is_numeric($n) && $n == (int) $n);
}

function dtlIntDiv($n, $d) {
  // * integer division
  // * $n = numerator, $d = denominator, both should be ints
  // * returns false for invalid input
  if (!dtlIsInt($n) || !dtlIsInt($d)) {
    return FALSE;
  }
  return (int) ($n / $d);
}

function dtlIsInRange($n, $min, $max) {
  // * returns true if $n is an integer in the range $min..$max inclusive
  return dtlIsInt($n) && $n >= $min && $n <= $max;
}

function dtlDigits($dts) {
  // * will remove any non-digit chars from a string
  $dts .= '';
  $result = '';
  for ($i = 0; $i < strlen($dts); $i++) {
    $ch = $dts{$i};
    if ($ch >= '0' && $ch <= '9') {
      $result .= $ch;
    }
  }
  return $result;
}

///////////////////////////////////////////////////////////////////////////
// constructors

function dtlDateStr($year = 0, $month = 0, $day = 0) {
  // * creates a DateStr from the parameters (0000-00-00 .. 9999-99-99)
  // * $year can be in the range 0..9999
  // * $month can be in the range 0..99
  // * $day can be in the range 0..99
  // * returns false if any parameters are out of range
  // * parameters do not have to represent a valid date, this function is just for building strings
  // * (therefore support is provided for 0000-00-00 (MySQL))
  // * to check if a date is valid use dtlIsValidDate()
  if (!dtlIsInt($year) || $year < 0 || $year > 9999 || !dtlIsInt($month)
    || $month < 0
    || $month > 99
    || !dtlIsInt($day)
    || $day < 0
    || $day > 99
  ) {
    return FALSE;
  }
  return dtlZeroPad($year, 4) . '-' . dtlZeroPad($month) . '-'
    . dtlZeroPad($day);
}

function dtlTimeStr($hours = 0, $minutes = 0, $seconds = 0) {
  // * creates a TimeStr from the parameters (00:00:00 .. 99:99:99)
  // * $hour can be in the range 0..99
  // * $minute can be in the range 0..99
  // * $second can be in the range 0..99
  // * returns false if any parameters are out of range
  // * parameters do not have to represent a valid time, this function is just for building strings
  // * to check if a time is valid, use dtlIsValidTime()
  if (!dtlIsInRange($hours, 0, 99) || !dtlIsInRange($minutes, 0, 99)
    || !dtlIsInRange($seconds, 0, 99)
  ) {
    return FALSE;
  }
  return dtlZeroPad($hours) . ':' . dtlZeroPad($minutes) . ':'
    . dtlZeroPad($seconds);
}

function dtlDateTimeStr($multi = '0000-00-00', $multi2 = '00:00:00', $day = 0,
  $hours = 0, $minutes = 0, $seconds = 0) {
  // * creates a DateTimeStr from the parameters
  // * syntax 1: dtlDateTimeStr($ds, $ts) makes a DateTimeStr from a DateStr and a TimeStr
  // * syntax 2: dtlDateTimeStr($year, $month, $day, $hours, $minutes, $seconds)
  // * parameter ranges as in dtlDateStr and dtlTimeStr
  // * no checking is performed for the validity of the datetime
  //   (to check if a datetime is valid, use dtlIsValidDateTime())
  // * returns false if invalid parameters
  if (dtlIsDateStr($multi) && dtlIsTimeStr($multi2)) {
    $ds = $multi;
    $ts = $multi2;
  }
  else // assume integers - dtlDateStr and dtlTimeStr will check type and range
  {
    $year = $multi;
    $month = $multi2;
    $ds = dtlDateStr($year, $month, $day);
    if (!$ds) {
      return FALSE;
    }
    $ts = dtlTimeStr($hours, $minutes, $seconds);
    if (!$ts) {
      return FALSE;
    }
  }
  return $ds . ' ' . $ts;
}

///////////////////////////////////////////////////////////////////////////
// * functions for getting the local date, time or datetime
// * these functions return strings

function dtlLocalDate() {
  // * returns current local date as a DateStr
  return date('Y-m-d');
}

function dtlLocalTime() {
  // * returns current local time of day as a TimeStr
  return date('H:i:s');
}

function dtlLocalDateTime() {
  // * returns current local datetime as a DateTimeStr
  return date('Y-m-d H:i:s');
}

function dtlToday() {
  // * synonym for dtlLocalDate()
  return dtlLocalDate();
}

function dtlNow() {
  // * synonyn for dtlLocalDateTime()
  return dtlLocalDateTime();
}

function dtlCurrentYear() {
  // * returns the current year:
  return date('Y');
}

function dtlCurrentMonth() {
  // * returns the current month:
  return date('m');
}

///////////////////////////////////////////////////////////////////////////
// Supplementary functions related to local date.

/**
 * Gets tomorrow's dates.
 *
 * @return string
 */
function dtlTomorrow() {
  return dtlAdd(dtlToday(), 1, 'day');
}

/**
 * Gets yesterday's dates.
 *
 * @return string
 */
function dtlYesterday() {
  return dtlAdd(dtlToday(), -1, 'day');
}

/**
 * Gets the datetime at the beginning of (midnight) today.
 *
 * @return string
 */
function dtlTodayBegin() {
  return dtlToday() . ' 00:00:00';
}

/**
 * Gets the datetime at noon today.
 *
 * @return string
 */
function dtlTodayNoon() {
  return dtlToday() . ' 12:00:00';
}

/**
 * Gets the datetime at midnight today.
 * (i.e. beginning of tomorrow)
 *
 * @return string
 */
function dtlTodayEnd() {
  return dtlTomorrow() . ' 00:00:00';
}

///////////////////////////////////////////////////////////////////////////
// * functions for getting the global (UTC) date, time or datetime
// * uses system settings to determine timezone offset
// * these functions return strings
// * currently no support for leap seconds

function dtlGlobalDate() {
  // * returns current global (UTC) date as a DateStr
  return gmdate('Y-m-d');
}

function dtlGlobalTime() {
  // * returns current global (UTC) time of day as a TimeStr
  return gmdate('H:i:s');
}

function dtlGlobalDateTime() {
  // * returns current global (UTC) datetime as a DateTimeStr
  return gmdate('Y-m-d H:i:s');
}

///////////////////////////////////////////////////////////////////////////
// functions to check if strings are in the proper format

function dtlIsDateStr($ds) {
  // * returns true if the DateStr $ds is a string in the format YYYY-MM-DD
  // * does not check for validity of the date (use dtlIsValidDate())
  return is_string($ds) && strlen($ds) == 10
    && preg_match('/\d\d\d\d-\d\d-\d\d/', $ds) == 1;
}

function dtlIsTimeStr($ts) {
  // * returns true if the TimeStr $ts is a string in the format HH:mm:ss
  // * does not check for validity of the time (use dtlIsValidTime())
  return is_string($ts) && strlen($ts) == 8
    && preg_match('/\d\d:\d\d:\d\d/', $ts) == 1;
}

function dtlIsDateTimeStr($dts) {
  // * returns true if the DateTimeStr $dts is a string in the format YYYY-MM-DD HH:mm:ss
  // * does not check for validity of the datetime (use dtlIsValidDateTime())
  return is_string($dts) && strlen($dts) == 19
    && preg_match('/\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d/', $dts) == 1;
}

///////////////////////////////////////////////////////////////////////////
// functions to check if string represents a 'zero' date, time, or datetime

function dtlIsZero($dts) {
  if (dtlIsDateStr($dts)) {
    return $dts == '0000-00-00';
  }
  else {
    if (dtlIsTimeStr($dts)) {
      return $dts == '00:00:00';
    }
    else {
      if (dtlIsDateTimeStr($dts)) {
        return $dts == '0000-00-00 00:00:00';
      }
      else {
        return FALSE;
      }
    }
  }
}

///////////////////////////////////////////////////////////////////////////
// * conversion from strings to Unix Timestamps

function dtlDateStrToUTS($ds) {
  // * converts DateStr to a Unix Timestamp
  // * supports most formats, not just YYYY-MM-DD
  // * only works if the date is valid and >= 1970-01-01
  // * returns false for invalid entry
  $unixTimestamp = strtotime($ds);
  return $unixTimestamp == -1 ? FALSE : $unixTimestamp;
}

function dtlTimeStrToUTS($ts) {
  // * converts a TimeStr to a Unix Timestamp
  // * uses the current local date for the date part
  // * returns false for invalid entry
  $unixTimestamp = strtotime($ts);
  return $unixTimestamp == -1 ? FALSE : $unixTimestamp;
}

function dtlDateTimeStrToUTS($dts) {
  // * converts a DateTimeStr to a Unix Timestamp
  // * supports most formats, not just YYYY-MM-DD HH:mm:ss
  // * only works if the DateTime is valid and >= 1970-01-01 00:00:00 UTC
  // * returns false for invalid entry
  $unixTimestamp = strtotime($dts);
  return $unixTimestamp == -1 ? FALSE : $unixTimestamp;
}

///////////////////////////////////////////////////////////////////////////
// conversion from Unix Timestamps to strings

function dtlUTSToDateStr($unixTimestamp) {
  // * converts a Unix Timestamp to a DateStr
  // * if the time of day is also recorded in the UTS, this info is lost
  // * returns false for invalid input
  $dta = @getdate($unixTimestamp);
  if (!$dta) {
    return FALSE;
  }
  return dtlDateStr($dta['year'], $dta['mon'], $dta['mday']);
}

function dtlUTSToTimeStr($unixTimestamp) {
  // * converts a Unix Timestamp to a TimeStr
  // * if the date is also recorded in the UTS, this info is lost
  // * this function does not account for the timezone of the local machine,
  //   therefore it will give a different result than date('H:i:s', $UTS)
  // * returns false for invalid input
  if (!dtlIsInt($unixTimestamp) || $unixTimestamp < 0) {
    return FALSE;
  }
  $seconds = $unixTimestamp % 86400;
  $hours = (int) ($seconds / 3600);
  $seconds -= ($hours * 3600);
  $minutes = (int) ($seconds / 60);
  $seconds -= ($minutes * 60);
  return dtlTimeStr($hours, $minutes, $seconds);
}

function dtlUTSToDateTimeStr($unixTimestamp) {
  // * converts a Unix Timestamp to a DateStr
  // * returns false for invalid input
  $dta = @getdate($unixTimestamp);
  if (!$dta) {
    return FALSE;
  }
  return dtlDateTimeStr($dta['year'], $dta['mon'], $dta['mday'], $dta['hours'],
    $dta['minutes'], $dta['seconds']);
}

///////////////////////////////////////////////////////////////////////////
// conversion from arrays to strings

function dtlDateArrayToStr($da) {
  // * returns DateStr constructed from the DateArray
  // * also works for DateTimeArrays but the time info is lost
  // * returns false for invalid input
  if (!is_array($da) || !isset($da['year']) || !isset($da['month'])
    || !isset($da['$day'])
  ) {
    return FALSE;
  }
  return dtlDateStr($da['year'], $da['month'], $da['day']);
}

function dtlTimeArrayToStr($ta) {
  // * returns TimeStr constructed from the TimeArray
  // * also works for DateTimeArrays but the date info is lost
  // * returns false for invalid input
  if (!is_array($ta) || !isset($ta['hours']) || !isset($ta['minutes'])
    || !isset($ta['$seconds'])
  ) {
    return FALSE;
  }
  return dtlTimeStr($ta['hours'], $ta['minutes'], $ta['seconds']);
}

function dtlDateTimeArrayToStr($dta) {
  // * returns DateTimeStr constructed from the DateTimeArray
  // * returns false for invalid input
  if (!is_array($dta) || !isset($dta['year']) || !isset($dta['month'])
    || !isset($dta['$day'])
    || !isset($dta['hours'])
    || !isset($dta['minutes'])
    || !isset($dta['$seconds'])
  ) {
    return FALSE;
  }
  return dtlDateTimeStr($dta['year'], $dta['month'], $dta['day'], $dta['hours'],
    $dta['minutes'], $dta['seconds']);
}

///////////////////////////////////////////////////////////////////////////
// functions to get indidividual parts of dates, times, datetimes

function dtlDateSplit($ds, &$year, &$month, &$day) {
  // * sets the value of $year, $month and $day to match the date in $ds
  if (!dtlIsDateStr($ds)) {
    return FALSE;
  }
  $dateParts = explode('-', $ds);
  $year = (int) $dateParts[0];
  $month = (int) $dateParts[1];
  $day = (int) $dateParts[2];
}

function dtlTimeSplit($ts, &$hours, &$minutes, &$seconds) {
  // * sets the value of $hours, $minutes and $seconds to match the time in $ts
  if (!dtlIsTimeStr($ts)) {
    return FALSE;
  }
  $timeParts = explode(':', $ts);
  $hours = (int) $timeParts[0];
  $minutes = (int) $timeParts[1];
  $seconds = (int) $timeParts[2];
}

function dtlDateTimeSplit($dts, &$date, &$time) {
  // * sets the value of $date and $time to match the datetime in $dts
  if (!dtlIsDateTimeStr($dts)) {
    return FALSE;
  }
  list($date, $time) = explode(' ', $dts);
}

///////////////////////////////////////////////////////////////////////////
// conversion from strings to arrays

function dtlDateStrToArray($ds) {
  // * returns an array containing the different date parts
  // * works with any DateStr in the format YYYY-MM-DD, even if invalid (e.g. 0000-00-00)
  // * returns false if $ds not in the proper format
  if (!dtlIsDateStr($ds)) {
    return FALSE;
  }
  dtlDateSplit($ds, $year, $month, $day);
  $da = [];
  $da['date'] = $ds;
  $da['year'] = $year;
  $da['month'] = $month;
  $da['day'] = $day;
  return $da;
}

function dtlTimeStrToArray($ts) {
  // * returns an array containing the different time parts
  // * works with any TimeStr in the format HH:mm:ss, even if invalid (e.g. 99:99:99)
  // * returns false if $ts not in the proper format
  if (!dtlIsTimeStr($ts)) {
    return FALSE;
  }
  $timeParts = explode(':', $ts);
  $ta = [];
  $ta['time'] = $ts;
  $ta['hours'] = (int) $timeParts[0];
  $ta['minutes'] = (int) $timeParts[1];
  $ta['seconds'] = (int) $timeParts[2];
  return $ta;
}

function dtlDateTimeStrToArray($dts) {
  // * returns an array containing the different datetime parts
  // * works with any DateTimeStr in the format YYYY-MM-DD HH:mm:ss, even if invalid
  // * returns false if $dts not in the proper format
  if (!dtlIsDateTimeStr($dts)) {
    return FALSE;
  }
  $dtParts = explode(' ', $dts);
  return array_merge(dtlDateStrToArray($dtParts[0]),
    dtlTimeStrToArray($dtParts[1]));
}

/**
 * General purpose conversion from date/time string to array.
 * Can accept a date, time, or datetime string.
 *
 * @param string $dts
 *
 * @return string[]
 */
function dtlStrToArray($dts) {
  if (dtlIsDateStr($dts)) {
    return dtlDateStrToArray($dts);
  }
  elseif (dtlIsTimeStr($dts)) {
    return dtlTimeStrToArray($dts);
  }
  elseif (dtlIsDateTimeStr($dts)) {
    return dtlDateTimeStrToArray($dts);
  }
  else {
    return FALSE;
  }
}

///////////////////////////////////////////////////////////////////////////
// conversion between DateStr, Modified Julian Date (MJD), and Julian Date (JD)

function dtlDateToMJD($ds) {
  // * returns a Modified Julian Date given a valid Gregorian date as a DateStr
  // * returns false for invalid input
  if (!dtlIsValidDate($ds)) {
    return FALSE;
  }
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

///////////////////////////////////////////////////////////////////////////
// conversion between TimeStr and seconds

function dtlTimeStrToSeconds($ts) {
  // * returns number of seconds represented by the TimeStr $ts
  // * doesn't have to be a valid TimeStr, therefore a maximum of 99:99:99 is permitted
  //   (which would return 99 * 3600 + 99 * 60 + 99 = 362439)
  // * returns false for invalid input
  if (!dtlIsTimeStr($ts)) {
    return FALSE;
  }
  $ta = explode(':', $ts);
  return ($ta[0] * 3600) + ($ta[1] * 60) + $ta[2];
}

function dtlSecondsToTimeStr($seconds) {
  // * because this could be a duration, the hours part of the result can be up to 99
  // * only supports 0 (00:00:00) .. 359999 (99:59:59) seconds
  // * returns false for invalid input
  $maxSeconds = (99 * 3600) + (59 * 60) + 59;
  if (!dtlIsInt($seconds) || !dtlIsInRange($seconds, 1, $maxSeconds)) {
    return FALSE;
  }
  $hours = (int) ($seconds / 3600);
  $seconds -= $hours * 3600;
  $minutes = (int) ($seconds / 60);
  $seconds -= $minutes * 60;
  return dtlTimeStr($hours, $minutes, $seconds);
}

///////////////////////////////////////////////////////////////////////////
// support functions to process multi-purpose parameters into their parts
// * these functions make it possible to provide multiple syntaxes for some functions

function dtlProcessDateParams($multi, &$year, &$month, &$day) {
  // * according to the type of $multi, finds the values for $year, $month, and $day
  // * returns true if the values could be found, otherwise false
  // * doesn't check for validity
  if ($da = dtlDateStrToArray($multi)) // returns false if $multi not a DateStr
  {
    // syntax 1: multi is a DateStr
    extract($da); // sets $year = $da['year'], etc.
    return TRUE;
  }
  else {
    if (dtlIsInt($multi) && dtlIsInt($month) && dtlIsInt($day)) {
      // syntax 2: multi is the year
      $year = $multi;
      return TRUE;
    }
    else {
      if (is_array($multi) && count($multi) == 3 && dtlIsInt($multi['year'])
        && dtlIsInt($multi['month'])
        && dtlIsInt($multi['day'])
      ) {
        extract($multi); // sets $year = $multi['year'], etc.
        return TRUE;
      }
      else // dodgy input
      {
        return FALSE;
      }
    }
  }
}

function dtlProcessTimeParams($multi, &$hours, &$minutes, &$seconds) {
  // * according to the type of $multi, finds the values for $hours, $minutes, and $seconds
  // * returns true if the values could be found (doesn't check for validity)
  // * returns false if values couldn't be found
  if ($ta = dtlTimeStrToArray($multi)) // returns false if $multi not a TimeStr
  {
    // syntax 1: multi is a TimeStr
    extract($ta); // sets $hours = $ta['hours'], etc.
    return TRUE;
  }
  else {
    if (dtlIsInt($multi) && dtlIsInt($minutes) && dtlIsInt($seconds)) {
      // syntax 2: multi is the $hours
      $hours = $multi;
      return TRUE;
    }
    else {
      if (is_array($multi) && count($multi) == 3 && dtlIsInt($multi['hours'])
        && dtlIsInt($multi['minutes'])
        && dtlIsInt($multi['seconds'])
      ) {
        extract($multi); // sets $hours = $multi['hours'], etc.
        return TRUE;
      }
      else // dodgy input
      {
        return FALSE;
      }
    }
  }
}

function dtlProcessDateTimeParams($multi, &$year, &$month, &$day, &$hours,
  &$minutes, &$seconds) {
  // * according to the type of $multi, finds the parts of the datetime
  // * returns true if the values could be found (doesn't check for validity)
  // * returns false if values couldn't be found
  if ($dta = dtlDateTimeStrToArray($multi)) // returns false if $multi not a DateTimeStr
  {
    // syntax 1: multi is a DateTimeStr
    extract($dta); // sets $year = $dta['year'], etc.
    return TRUE;
  }
  else {
    if (dtlIsInt($multi) && dtlIsInt($month) && dtlIsInt($day)
      && dtlIsInt($hours)
      && dtlIsInt($minutes)
      && dtlIsInt($seconds)
    ) {
      // syntax 2: multi is the year
      $year = $multi;
      return TRUE;
    }
    else {
      if (is_array($multi) && count($multi) == 6 && dtlIsInt($multi['year'])
        && dtlIsInt($multi['month'])
        && dtlIsInt($multi['day'])
        && dtlIsInt($multi['hours'])
        && dtlIsInt($multi['minutes'])
        && dtlIsInt($multi['seconds'])
      ) {
        extract($multi); // sets $year = $multi['year'], etc.
        return TRUE;
      }
      else // dodgy input
      {
        return FALSE;
      }
    }
  }
}

///////////////////////////////////////////////////////////////////////////
// functions to check if parameters represent valid dates/times/datetimes

function dtlIsValidYear($year, $count1582 = FALSE) {
  // return true if $year is a valid year
  // max is 9999; min is 1583 by default
  // if $count1582 is true then min is 1582
  return dtlIsInt($year) && $year >= ($count1582 ? 1582 : 1583)
    && $year <= 9999;
}

function dtlIsValidMonth($year, $month, $countOct1582 = FALSE) {
  // * returns true if a valid Gregorian month, otherwise false
  // * by default the earliest valid month is November 1582,
  //   as this was the first full month of the Gregorian calendar
  // * if $countOct1582 is true then October 1582 counted as valid
  // * the latest valid Gregorian month (as far as the DTL is concerned) is December 9999
  // * returns false for invalid input
  if (!dtlIsInt($year) || !dtlIsInt($month)) {
    return FALSE;
  }
  if ($year < 1582 || $month < 1 || $month > 12) {
    return FALSE;
  }
  // 1582 is a special case:
  if ($year == 1582) {
    return $month >= ($countOct1582 ? 10 : 11);
  }
  return TRUE;
}

function dtlIsValidDate($multi, $month = 0, $day = 0, $proleptic = FALSE) {
  // * returns true if parameters represents a valid Gregorian date, otherwise false
  // * earliest valid date is 1582-10-15 and latest is 9999-12-31
  // * supports 3 syntaxes:
  //   dtlIsValidDate($ds) e.g. dtlIsValidDate('2002-12-28')
  //   dtlIsValidDate($year, $month, $day) e.g. dtlIsValidDate(2002, 12, 28)
  //   dtlIsValidDate($da) where $da is a DateArray
  // * $multi is therefore a multi-purpose parameter
  if (!dtlProcessDateParams($multi, $year, $month, $day)) {
    return FALSE;
  }
  if (!dtlIsInRange($month, 1, 12)) {
    return FALSE;
  }
  $minDay = 1;
  if (!$proleptic) {
    // earliest Gregorian date is 1582-10-15:
    if ($year < 1582) {
      return FALSE;
    }
    else {
      if ($year == 1582) {
        if ($month < 10) {
          return FALSE;
        }
        else {
          if ($month == 10) {
            $minDay = 15;
          }
        }
      }
    }
  }
  return dtlIsInRange($day, $minDay, dtlDaysInMonth($year, $month, TRUE));
}

function dtlIsValidTime($multi, $minutes = 0, $seconds = 0, $duration = FALSE,
  $allowLeapSeconds = FALSE) {
  // * returns true if parameters represents a valid time (time of day or duration), otherwise false
  // * if $duration is false then hours must be in range 0..23
  // * $minutes must be in range 0..59
  // * if $allowLeapSeconds is true then $seconds can be from 0..60, otherwise 0..59
  // * supports 3 syntaxes:
  //   dtlIsValidTime($ts) e.g. dtlIsValidTime('09:01:11')
  //   dtlIsValidTime($hours, $minutes, $seconds) e.g. dtlIsValidTime(9, 1, 11)
  //   dtlIsValidTime($ta) where $ta is a TimeArray
  // * $multi is therefore a multi-purpose parameter
  if (!dtlProcessTimeParams($multi, $hours, $minutes, $seconds)) {
    return FALSE;
  }
  if (!$duration && !dtlIsInRange($hours, 0, 23)) {
    return FALSE;
  }
  return dtlIsInRange($minutes, 0, 59)
    && dtlIsInRange($seconds, 0, $allowLeapSeconds ? 60 : 59);
}

function dtlIsValidDateTime($multi, $month = 0, $day = 0, $hours = 0,
  $minutes = 0, $seconds = 0, $proleptic = FALSE) {
  // * returns true if parameters represents a valid Gregorian datetime, otherwise false
  // * earliest valid date is 1582-11-01 and latest is 9999-12-31
  // * earliest valid time is 00:00:00 and latest is 23:59:60
  // * leap seconds supported, i.e. 23:59:60 valid for certain dates
  // * supports 3 syntaxes:
  //   dtlIsValidDateTime($dts) e.g. dtlIsValidDateTime('2002-28-12 09:27:03')
  //   dtlIsValidDateTime($year, $month, $day, $hours, $minutes, $seconds)
  //   dtlIsValidDateTime($dta) where $dta is a DateTimeArray
  // * $multi is therefore a multi-purpose parameter
  if (!dtlProcessDateTimeParams($multi, $year, $month, $day, $hours, $minutes,
    $seconds)
  ) {
    return FALSE;
  }
  $dateValid = dtlIsValidDate($year, $month, $day, $proleptic);
  if ($hours == 23 && $minutes == 59 && $seconds == 60) {
    $timeValid = dtlHadLeapSecond(dtlDateStr($year, $month, $day));
  }
  else {
    $timeValid = dtlIsValidTime($hours, $minutes, $seconds);
  }
  return $dateValid && $timeValid;
}

function dtlAdjust(&$param1, &$param2, $min, $max) {
  // * shifts params until $param2 is between $min and $max
  // * specifically for the dtlMakeValid functions
  if (dtlIsInRange($param2, $min, $max)) {
    return;
  }
  $rangeWidth = $max - $min + 1;
  while ($param2 < $min) {
    $param2 += $rangeWidth;
    $param1--;
  }
  while ($param2 > $max) {
    $param2 -= $rangeWidth;
    $param1++;
  }
}

function dtlMakeDateValid(&$year, &$month, &$day, $proleptic = FALSE) {
  // * if the params represent an invalid date, they are logically adjusted so that they do
  // * e.g. 2002-13-12 => 2003-01-12
  // * e.g. 2002-12-00 => 2002-11-30
  // * e.g. 2002-12-33 => 2003-01-02
  // * e.g. 2002-13-33 => 2003-01-33 => 2003-02-02
  // * return true if the date can be made valid, otherwise false

  // check for valid input:
  if (!dtlIsInt($year) || !dtlIsInt($month) || !dtlIsInt($day)) {
    return FALSE;
  }

  // do we have to do anything?
  if (dtlIsValidDate($year, $month, $day, $proleptic)) {
    return TRUE;
  }

  // make some temp variables,
  // because we don't want to update the params unless the date can be made valid:
  $year2 = $year;
  $month2 = $month;
  $day2 = $day;

  // adjust month and year:
  dtlAdjust($year2, $month2, 1, 12);

  // adjust $day:
  $dim = dtlDaysInMonth($year2, $month2, TRUE);
  if (!dtlIsInRange($day2, 1, $dim)) {
    // is day too low?
    while ($day2 < 1) {
      // go to previous month:
      $month2--;
      if ($month2 == 0) {
        $month2 = 12;
        $year2--;
      }
      $dim = dtlDaysInMonth($year2, $month2, TRUE);
      $day2 += $dim;
    }

    while ($day2 > $dim) {
      // go to next month:
      $month2++;
      if ($month2 == 13) {
        $month2 = 1;
        $year2++;
      }
      $day2 -= $dim;
      $dim = dtlDaysInMonth($year2, $month2, TRUE);
    }
  }

  if ($valid = dtlIsValidDate($year2, $month2, $day2, $proleptic)) {
    $year = $year2;
    $month = $month2;
    $day = $day2;
  }
  return $valid;
}

function dtlMakeTimeValid(&$hours, &$minutes, &$seconds, $duration = FALSE) {
  // * if minutes or seconds are out of range,
  //   params are logically adjusted so that they are in range
  // * params can be negative
  // * resulting hours could be out of range for time of day, i.e. < 0 or > 23

  // check param types:
  if (!dtlIsInt($hours) || !dtlIsInt($minutes) || !dtlIsInt($seconds)) {
    return FALSE;
  }

  // do we have to do anything?
  if (dtlIsValidTime($hours, $minutes, $seconds, $duration)) {
    return TRUE;
  }

  $seconds2 = ($hours * 3600) + ($minutes * 60) + $seconds;
  $minutes2 = (int) ($seconds2 / 60);
  $seconds2 -= $minutes2 * 60;
  $hours2 = (int) ($minutes2 / 60);
  $minutes2 -= $hours2 * 60;

  if ($duration) {
    $valid = TRUE;
  }
  else {
    $valid = dtlIsInRange($hours2, 0, 23) && dtlIsInRange($minutes2, 0, 59)
      && dtlIsInRange($seconds2, 0, 59);
  }
  if ($valid) {
    $hours = $hours2;
    $minutes = $minutes2;
    $seconds = $seconds2;
  }
  return $valid;
}

function dtlMakeDateTimeValid(&$year, &$month, &$day, &$hours, &$minutes,
  &$seconds, $proleptic = FALSE) {
  // * logically adjusts the values of the parameters in order to provide a valid datetime if possible
  // * leap seconds not supported

  // check param types:
  if (!dtlIsInt($year) || !dtlIsInt($month) || !dtlIsInt($day)
    || !dtlIsInt($hours)
    || !dtlIsInt($minutes)
    || !dtlIsInt($seconds)
  ) {
    return FALSE;
  }

  // do we need to do anything?
  if (dtlIsValidDateTime($year, $month, $day, $hours, $minutes, $seconds,
    $proleptic)) {
    return TRUE;
  }

  // make some temp variables,
  // because we don't want to update the params unless the datetime can be made valid:
  $year2 = $year;
  $month2 = $month;
  $day2 = $day;
  $hours2 = $hours;
  $minutes2 = $minutes;
  $seconds2 = $seconds;

  dtlAdjust($minutes2, $seconds2, 0, 59);
  dtlAdjust($hours2, $minutes2, 0, 59);
  dtlAdjust($day2, $hours2, 0, 23);
  if ($valid = dtlMakeDateValid($year2, $month2, $day2, $proleptic)) {
    // update params:
    $year = $year2;
    $month = $month2;
    $day = $day2;
    $hours = $hours2;
    $minutes = $minutes2;
    $seconds = $seconds2;
  }
  return $valid;
}

function dtlValidDateStr($year, $month, $day) {
  // * same as dtlDateStr but returns valid DateStr if possible,
  //   i.e. if any parameters are out of range but can be logically adjusted to make a valid date
  //   then the valid date is returned
  if (dtlMakeDateValid($year, $month, $day)) {
    return dtlDateStr($year, $month, $day);
  }
  else {
    return FALSE;
  }
}

function dtlValidTimeStr($hours, $minutes, $seconds) {
  // * same as dtlTimeStr but returns valid TimeStr if possible,
  //   i.e. if any parameters are out of range but can be logically adjusted to make a valid time
  //   then the valid time is returned
  if (dtlMakeTimeValid($hours, $minutes, $seconds)) {
    return dtlTimeStr($hours, $minutes, $seconds);
  }
  else {
    return FALSE;
  }
}

function dtlValidDateTimeStr($year, $month, $day, $hours, $minutes, $seconds) {
  // * same as dtlDateTimeStr but returns valid DateTimeStr if possible,
  //   i.e. if any parameters are out of range but can be logically adjusted to make a valid
  //   datetime then the valid datetime is returned
  if (dtlMakeDateTimeValid($year, $month, $day, $hours, $minutes, $seconds)) {
    return dtlDateTimeStr($year, $month, $day, $hours, $minutes, $seconds);
  }
  else {
    return FALSE;
  }
}

///////////////////////////////////////////////////////////////////////////
// constants for days of the week

/**
 * ISO8601
 * The DTL conforms to ISO8601 spec for handling weeks and days of the week.
 * - Days numbered from 1-7, starting with Monday.
 * - The first week of the year contains 4 or more days.
 */

// * these constants match the array indices in $dtlLanguages[]['dayNames']
// * therefore you can get a day name with (e.g.) $dtlLanguages['EN']['dayNames'][DTL_THURSDAY]
// * Numbering days from 1-7 starting with Monday comes from ISO8601.
define("DTL_MONDAY", 1);
define("DTL_TUESDAY", 2);
define("DTL_WEDNESDAY", 3);
define("DTL_THURSDAY", 4);
define("DTL_FRIDAY", 5);
define("DTL_SATURDAY", 6);
define("DTL_SUNDAY", 7);

///////////////////////////////////////////////////////////////////////////
// Get functions - part 1
// ======================
// This first set of functions do *not* require a valid DateStr, TimeStr, or DateTimeStr,
// just so long as they are in the correct format.

function dtlGetDate($dts = '') {
  // * given a DateStr or DateTimeStr, returns the date part
  // * date not checked for validity
  // * if no params, returns current date
  // * returns false for invalid input
  if ($dts == '') {
    return dtlToday();
  }
  else {
    if (dtlIsDateStr($dts)) {
      return $dts;
    }
    else {
      if (dtlIsDateTimeStr($dts)) {
        return substr($dts, 0, 10);
      }
      else {
        return FALSE;
      }
    }
  }
}

function dtlGetTime($dts = '') {
  // * given a TimeStr or DateTimeStr, returns the time part
  // * if no params, returns current time
  // * time not checked for validity
  // * returns false for invalid input
  if ($dts == '') {
    return dtlLocalTime();
  }
  else {
    if (dtlIsTimeStr($dts)) {
      return $dts;
    }
    else {
      if (dtlIsDateTimeStr($dts)) {
        return substr($dts, 11, 8);
      }
      else {
        return FALSE;
      }
    }
  }
}

function dtlGetYear($dts = '') {
  // * returns the year as an integer given a DateStr or a DateTimeStr
  // * if no params, returns current year
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  elseif (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
    return FALSE;
  }
  return (int) substr($dts, 0, 4);
}

function dtlGetYear2Digit($dts = '') {
  // * returns the year as a 2-digit zero-padded string given a DateStr or a DateTimeStr
  // * if no params, returns current year in 2-digit format
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  return substr($dts, 2, 2);
}

function dtlGetQuarter($dts = '') {
  // * returns the quarter as an integer (1..4) given a DateStr or a DateTimeStr
  // * if no params, returns current quarter
  // * if month = 0, quarter = 0
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $month = (int) substr($dts, 5, 2);
  return (int) (($month - 1) / 3) + 1;
}

function dtlGetMonth($dts = '') {
  // * returns the month as an integer given a DateStr or a DateTimeStr
  // * if no params, returns current month
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  return (int) substr($dts, 5, 2);
}

function dtlGetMonthName($dts = '', $languageCode = '') {
  // * returns the month name in the current language given a DateStr or a DateTimeStr
  // * if no params, returns current month name
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $month = (int) substr($dts, 5, 2);
  return dtlMonthName($month, $languageCode);
}

function dtlGetAbbrevMonthName($dts = '', $languageCode = '', $nChars = 3) {
  // * returns the abbreviated month name in the current language
  //   given a DateStr or a DateTimeStr
  // * if no params, returns current abbrev month name
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $month = (int) substr($dts, 5, 2);
  return dtlAbbrevMonthName($month, $languageCode, $nChars);
}

function dtlGetDay($dts = '') {
  // * returns the day of the month as an integer (1..31) given a DateStr or a DateTimeStr
  // * if no params, returns current day of month
  // * returns false if $dts not a DateStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  return (int) substr($dts, 8, 2);
}

function dtlGetHours($dts = '') {
  // * given a TimeStr or a DateTimeStr, return the hours
  // * if no params, returns current hour
  // * returns false if $dts not a TimeStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlNow();
  }
  else {
    if (!dtlIsTimeStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $ts = dtlGetTime($dts);
  return (int) substr($ts, 0, 2);
}

function dtlGetMinutes($dts = '') {
  // * given a TimeStr or a DateTimeStr, return the minutes
  // * if no params, returns current minute
  // * returns false if $dts not a TimeStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlNow();
  }
  else {
    if (!dtlIsTimeStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $ts = dtlGetTime($dts);
  return (int) substr($ts, 3, 2);
}

function dtlGetSeconds($dts = '') {
  // * given a TimeStr or a DateTimeStr, return the seconds
  // * if no params, returns current second
  // * returns false if $dts not a TimeStr or DateTimeStr (no validity checking)
  if ($dts == '') {
    $dts = dtlNow();
  }
  else {
    if (!dtlIsTimeStr($dts) && !dtlIsDateTimeStr($dts)) {
      return FALSE;
    }
  }
  $ts = dtlGetTime($dts);
  return (int) substr($ts, 6);
}

///////////////////////////////////////////////////////////////////////////
// Get functions - part 2
// ======================
// This second set of 'get' functions *do* require a valid DateStr, TimeStr, or DateTimeStr
// in order to calculate the result.

function dtlGetWeekOfYear($dts = '') {
  // * given a DateStr or a DateTimeStr, calculate which week of the year it is (1..53)
  // * uses the method specified in $dtlFirstWeekOfYear
  // * if no params, returns current week of year
  // * returns false if $dts not a valid date or datetime
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
  }
  $ds = dtlGetDate($dts);
  $dayOfYear = dtlGetDayOfYear($ds);
  $dayOfWeek = dtlGetDayOfWeek($ds);
  // This formula gives the first whole week as week 1:
  $week = intdiv($dayOfYear - $dayOfWeek + 7, 7);
  // Add 1 if the year started on Tuesday, Wednesday or Thursday:
  $year = dtlGetYear($ds);
  $firstDayOfYear = dtlFirstDayOfYear($year);
  $firstDayOfYear_dayOfWeek = dtlGetDayOfWeek($firstDayOfYear);
  if ($firstDayOfYear_dayOfWeek >= DTL_TUESDAY
    && $firstDayOfYear_dayOfWeek <= DTL_THURSDAY
  ) {
    $week++;
  }
  // Check for beginning of year:
  if ($week == 0) {
    $year--;
    $lastDayOfPreviousYear = dtlLastDayOfYear($year);
    return dtlGetWeekOfYear($lastDayOfPreviousYear);
  }
  // Check for end of year:
  $daysInYear = dtlDaysInYear($year);
  $lastDayOfYear = dtlLastDayOfYear($year);
  $lastDayOfYear_dayOfWeek = dtlGetDayOfWeek($lastDayOfYear);
  if ($lastDayOfYear_dayOfWeek <= 3
    && $daysInYear - $dayOfYear < $lastDayOfYear_dayOfWeek
  ) {
    // It's the first week of the following year:
    $year++;
    $week = 1;
  }
  return ['year' => $year, 'week' => $week];
}

function dtlGetDayOfYear($dts = '') {
  // * returns the day of the year as an integer (1..366) given a DateStr or a DateTimeStr
  // * if no params, returns current day of year
  // * returns false if $dts not a valid date or datetime
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
  }
  $da = dtlDateStrToArray(dtlGetDate($dts));
  if ($da['month'] == 1) {
    return $da['day'];
  }
  else {
    return dtlDaysInMonths($da['year'], $da['month'] - 1) + $da['day'];
  }
}

function dtlGetDayOfWeek($dts = '') {
  // * returns the day of the week as an integer (1..7) given a DateStr or a DateTimeStr
  // * if no params, returns current day of week
  // * returns false if $dts not a valid date or datetime
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
  }
  $mjd = dtlDateToMJD(dtlGetDate($dts));
  // make sure mjd is a positive number so the modulus will work:
  if ($mjd < 7) {
    $mjd += (ceil(abs($mjd - 7) / 7) + 1) * 7;
  }
  // MJD=0 is a Wednesday, have to adjust by the difference between Monday and Wednesday:
  $dow = ($mjd + DTL_WEDNESDAY - DTL_MONDAY) % 7 + 1;
  return $dow;
}

function dtlGetDayName($dts = '', $languageCode = '') {
  // * returns the name of the day of the week in the current language,
  //   given a DateStr or a DateTimeStr
  // * if no params, returns current day name
  // * returns false if $dts not a valid date or datetime
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
  }
  return dtlDayName(dtlGetDayOfWeek($dts), $languageCode);
}

function dtlGetAbbrevDayName($dts = '', $languageCode = '', $nChars = 0) {
  // * returns abbreviated name for the day of the week in the current language,
  //   given a DateStr or a DateTimeStr
  // * if no params, returns current abbrev day name
  // * returns false if $dts not a valid date or datetime
  if ($dts == '') {
    $dts = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
  }
  return dtlAbbrevDayName(dtlGetDayOfWeek($dts), $languageCode, $nChars);
}

///////////////////////////////////////////////////////////////////////////
// set functions:

///////////////////////////////////////////////////////////////////////////
// set date and time parts:

function dtlSetDate(&$dts, $ds) {
  // * if $dts is a DateStr then it is simply set to $ds (same effect as $dts = $ds)
  // * if $dts is a TimeStr then it is changed to a DateTimeStr with $ds as the date part
  // * if $dts is a DateTimeStr then the date part is set to $ds
  // * returns false if parameters not in proper formats
  // * parameters not checked for validity
  if (!dtlIsDateStr($ds)) {
    return FALSE;
  }
  if (dtlIsDateStr($dts)) {
    $dts = $ds;
    return TRUE;
  }
  else {
    if (dtlIsTimeStr($dts)) {
      $dts = $ds . ' ' . $dts;
      return TRUE;
    }
    else {
      if (dtlIsDateTimeStr($dts)) {
        $dts = $ds . ' ' . dtlGetTime($dts);
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }
}

function dtlSetTime(&$dts, $ts) {
  // * if $dts is a DateStr then it is changed to a DateTimeStr with $ts as the time part
  // * if $dts is a TimeStr then it is simply set to $ts (same effect as $dts = $ts)
  // * if $dts is a DateTimeStr then the time part is set to $ts
  // * returns false if parameters not in proper formats
  // * parameters not checked for validity
  if (!dtlIsTimeStr($ts)) {
    return FALSE;
  }
  if (dtlIsDateStr($dts)) {
    $dts .= ' ' . $ts;
    return TRUE;
  }
  else {
    if (dtlIsTimeStr($dts)) {
      $dts = $ts;
      return TRUE;
    }
    else {
      if (dtlIsDateTimeStr($dts)) {
        $dts = dtlGetDate($dts) . ' ' . $ts;
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }
}

///////////////////////////////////////////////////////////////////////////
// set date parts:
// * these do not require the param or result to be valid dates

function dtlSetYear(&$dts, $year) {
  // * given a DateStr or a DateTimeStr, set the year to a new value
  // * returns false if $dts is not a DateStr or DateTimeStr
  // * does not require that input or output be a valid Gregorian date
  // check for valid input:
  if (!dtlIsInt($year) || (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts))) {
    return FALSE;
  }
  // do we need to do anything?
  $da = dtlDateStrToArray(dtlGetDate($dts));
  if ($year == $da['year']) {
    return TRUE;
  }
  // change the year and make date valid if possible:
  $month = $da['month'];
  $day = $da['day'];
  dtlMakeDateValid($year, $month, $day, TRUE);
  // apply new date:
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

function dtlSetMonth(&$dts, $month) {
  // * given a DateStr or DateTimeStr, sets the month part:
  // * returns false if $dts is not a DateStr or DateTimeStr
  // * does not require that input or output be a valid Gregorian date
  // check for valid input:
  if (!dtlIsInt($month) || (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts))) {
    return FALSE;
  }
  // do we need to do anything?
  $da = dtlDateStrToArray(dtlGetDate($dts));
  if ($month == $da['month']) {
    return TRUE;
  }
  // change the month and make date valid if possible:
  $year = $da['year'];
  $day = $da['day'];
  dtlMakeDateValid($year, $month, $day, TRUE);
  // apply new date:
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

function dtlSetDay(&$dts, $day) {
  // * given a DateStr or DateTimeStr, sets the day of month:
  // * returns false if $dts is not a DateStr or DateTimeStr
  // * does not require that input or output be a valid Gregorian date
  // check for valid input:
  if (!dtlIsInt($day) || (!dtlIsDateStr($dts) && !dtlIsDateTimeStr($dts))) {
    return FALSE;
  }
  // do we need to do anything?
  $da = dtlDateStrToArray(dtlGetDate($dts));
  if ($day == $da['day']) {
    return TRUE;
  }
  // change the day of month and make date valid if possible:
  $year = $da['year'];
  $month = $da['month'];
  dtlMakeDateValid($year, $month, $day, TRUE);
  // apply new date:
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

///////////////////////////////////////////////////////////////////////////
// set other date params:
// * these function DO require the param and result to be valid dates

function dtlSetQuarter(&$dts, $quarter) {
  // * given a DateStr or DateTimeStr, sets the quarter:
  // * returns false if $dts is not a valid date or datetime
  //   or if result is not a valid date or datetime
  // check for valid input:
  if (!dtlIsInt($quarter)
    || !dtlIsValidDate($dts)
    && !dtlIsValidDateTime($dts)
  ) {
    return FALSE;
  }
  // do we need to do anything?
  $curQuarter = dtlGetQuarter($dts);
  if ($quarter == $curQuarter) {
    return TRUE;
  }
  // change the quarter:
  $da = dtlDateStrToArray(dtlGetDate($dts));
  $year = $da['year'];
  $month = $da['month'] + (3 * ($quarter - $curQuarter));
  $day = $da['day'];
  if (!dtlMakeDateValid($year, $month, $day)) {
    return FALSE;
  }
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

function dtlSetWeekOfYear(&$dts, $woy) {
  // * given a DateStr or DateTimeStr, sets the week:
  // * returns false if $dts is not a valid date or datetime
  //   or if result is not a valid date or datetime
  // check for valid input:
  if (!dtlIsInt($woy) || !dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  // do we need to do anything?
  $curWoy = dtlGetWeekOfYear($dts);
  if ($woy == $curWoy) {
    return TRUE;
  }
  // change the week of year:
  $da = dtlDateStrToArray(dtlGetDate($dts));
  $year = $da['year'];
  $month = $da['month'];
  $day = $da['day'] + (7 * ($woy - $curWoy));
  if (!dtlMakeDateValid($year, $month, $day)) {
    return FALSE;
  }
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

function dtlSetDayOfYear(&$dts, $doy) {
  // * given a DateStr or DateTimeStr, sets the day of year:
  // * returns false if $dts is not a valid date or datetime
  //   or if result is not a valid date or datetime
  // check for valid input:
  if (!dtlIsInt($doy) || !dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  // do we need to do anything?
  if ($doy == dtlGetDayOfYear($dts)) {
    return TRUE;
  }
  // change the day of year:
  $year = dtlGetYear($dts);
  $month = 1;
  $day = $doy;
  if (!dtlMakeDateValid($year, $month, $day)) {
    return FALSE;
  }
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

function dtlSetDayOfWeek(&$dts, $dow) {
  // * given a DateStr or DateTimeStr, sets the day of the week:
  // * returns false if $dts is not a valid date or datetime
  //   or if result is not a valid date or datetime
  // check for valid input:
  if (!dtlIsInt($dow) || !dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  // do we need to do anything?
  $curDow = dtlGetDayOfWeek($dts);
  if ($dow == $curDow) {
    return TRUE;
  }
  // change the day of week:
  $da = dtlDateStrToArray(dtlGetDate($dts));
  $year = $da['year'];
  $month = $da['month'];
  $day = $da['day'] - $curDow + $dow;
  if (!dtlMakeDateValid($year, $month, $day)) {
    return FALSE;
  }
  dtlSetDate($dts, dtlDateStr($year, $month, $day));
  return TRUE;
}

///////////////////////////////////////////////////////////////////////////
// set time parts:
// * these require the param and result to be valid times/datetimes
// but I might change this requirement later

function dtlSetHours(&$dts, $hours) {
  // * given a TimeStr or DateTimeStr, sets the hours:
  // * returns false if $dts is not a valid time or datetime
  //   or if result is not a valid time or datetime
  // check for valid input:
  if (!dtlIsInt($hours)) {
    return FALSE;
  }
  if (dtlIsValidTime($dts)) {
    $type = 'time';
  }
  else {
    if (dtlIsValidDateTime($dts)) {
      $type = 'datetime';
    }
    else {
      return FALSE;
    }
  }
  // do we need to do anything?
  $ta = dtlTimeStrToArray(dtlGetTime($dts));
  if ($hours == $ta['hours']) {
    return TRUE;
  }
  // change the hours:
  $minutes = $ta['minutes'];
  $seconds = $ta['seconds'];
  if ($type == 'time') {
    if (!dtlMakeTimeValid($hours, $minutes, $seconds)) {
      return FALSE;
    }
    $dts = dtlTimeStr($hours, $minutes, $seconds);
  }
  else {
    if ($type == 'datetime') {
      $da = dtlDateStrToArray(dtlGetDate($dts));
      $year = $da['year'];
      $month = $da['month'];
      $day = $da['day'];
      if (!dtlMakeDateTimeValid($year, $month, $day, $hours, $minutes,
        $seconds)
      ) {
        return FALSE;
      }
      $dts = dtlDateTimeStr($year, $month, $day, $hours, $minutes, $seconds);
    }
  }
  return TRUE;
}

function dtlSetMinutes(&$dts, $minutes) {
  // * given a TimeStr or DateTimeStr, sets the minutes:
  // * returns false if $dts is not a valid time or datetime
  //   or if result is not a valid time or datetime
  // check for valid input:
  if (!dtlIsInt($minutes)) {
    return FALSE;
  }
  if (dtlIsValidTime($dts)) {
    $type = 'time';
  }
  else {
    if (dtlIsValidDateTime($dts)) {
      $type = 'datetime';
    }
    else {
      return FALSE;
    }
  }
  // do we need to do anything?
  $ta = dtlTimeStrToArray(dtlGetTime($dts));
  if ($minutes == $ta['minutes']) {
    return TRUE;
  }
  // change the minutes:
  $hours = $ta['hours'];
  $seconds = $ta['seconds'];
  if ($type == 'time') {
    if (!dtlMakeTimeValid($hours, $minutes, $seconds)) {
      return FALSE;
    }
    $dts = dtlTimeStr($hours, $minutes, $seconds);
  }
  else {
    if ($type == 'datetime') {
      $da = dtlDateStrToArray(dtlGetDate($dts));
      $year = $da['year'];
      $month = $da['month'];
      $day = $da['day'];
      if (!dtlMakeDateTimeValid($year, $month, $day, $hours, $minutes,
        $seconds)
      ) {
        return FALSE;
      }
      $dts = dtlDateTimeStr($year, $month, $day, $hours, $minutes, $seconds);
    }
  }
  return TRUE;
}

function dtlSetSeconds(&$dts, $seconds) {
  // * given a TimeStr or DateTimeStr, sets the seconds:
  // * returns false if $dts is not a valid time or datetime
  //   or if result is not a valid time or datetime
  // check for valid input:
  if (!dtlIsInt($seconds)) {
    return FALSE;
  }
  if (dtlIsValidTime($dts)) {
    $type = 'time';
  }
  else {
    if (dtlIsValidDateTime($dts)) {
      $type = 'datetime';
    }
    else {
      return FALSE;
    }
  }
  // do we need to do anything?
  $ta = dtlTimeStrToArray(dtlGetTime($dts));
  if ($seconds == $ta['seconds']) {
    return TRUE;
  }
  // change the seconds:
  $hours = $ta['hours'];
  $minutes = $ta['minutes'];
  if ($type == 'time') {
    if (!dtlMakeTimeValid($hours, $minutes, $seconds)) {
      return FALSE;
    }
    $dts = dtlTimeStr($hours, $minutes, $seconds);
  }
  else {
    if ($type == 'datetime') {
      $da = dtlDateStrToArray(dtlGetDate($dts));
      $year = $da['year'];
      $month = $da['month'];
      $day = $da['day'];
      if (!dtlMakeDateTimeValid($year, $month, $day, $hours, $minutes,
        $seconds)
      ) {
        return FALSE;
      }
      $dts = dtlDateTimeStr($year, $month, $day, $hours, $minutes, $seconds);
    }
  }
  return TRUE;
}

///////////////////////////////////////////////////////////////////////////
// datetime arithmetic functions:
// * these functions can take negative parameters, to subtract periods.

function dtlAdd($dts, $n, $units = 'days') {
  // * general purpose add function
  // * accepts years, quarters, months, weeks, days, hours, minutes and seconds as units
  $units = strtolower($units);
  if ($units == 'years' || $units == 'year') {
    return dtlAddYears($dts, $n);
  }
  else {
    if ($units == 'quarters' || $units == 'quarter') {
      return dtlAddQuarters($dts, $n);
    }
    else {
      if ($units == 'months' || $units == 'month') {
        return dtlAddMonths($dts, $n);
      }
      else {
        if ($units == 'weeks' || $units == 'week') {
          return dtlAddWeeks($dts, $n);
        }
        else {
          if ($units == 'days' || $units == 'day') {
            return dtlAddDays($dts, $n);
          }
          else {
            if ($units == 'hours' || $units == 'hour') {
              return dtlAddHours($dts, $n);
            }
            else {
              if ($units == 'minutes' || $units == 'minute') {
                return dtlAddMinutes($dts, $n);
              }
              else {
                if ($units == 'seconds' || $units == 'second') {
                  return dtlAddSeconds($dts, $n);
                }
                else {
                  return FALSE;
                }
              }
            }
          }
        }
      }
    }
  }
}

function dtlAddYears($dts, $nYears) {
  // * returns $dts with $nYears added
  // * returns false if $dts is not a valid date/datetime
  //   or if result is not a valid date/datetime
  // * returns true if result is a valid date/datetime
  if (!dtlIsInt($nYears)
    || (!dtlIsValidDate($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetYear($dts, dtlGetYear($dts) + $nYears)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddQuarters($dts, $nQuarters) {
  // * returns $dts with $nQuarters added
  // * returns false if $dts is not a valid date/datetime
  //   or if result is not a valid date/datetime
  // * returns true if result is a valid date/datetime
  if (!dtlIsInt($nQuarters)
    || (!dtlIsValidDate($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetQuarter($dts, dtlGetQuarter($dts) + $nQuarters)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddMonths($dts, $nMonths) {
  // * returns $dts with $nMonths added
  // * returns false if $dts is not a valid date/datetime
  //   or if result is not a valid date/datetime
  // * returns true if result is a valid date/datetime
  if (!dtlIsInt($nMonths)
    || (!dtlIsValidDate($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetMonth($dts, dtlGetMonth($dts) + $nMonths)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddWeeks($dts, $nWeeks) {
  // * returns $dts with $nWeeks added
  // * returns false if $dts is not a valid date/datetime
  //   or if result is not a valid date/datetime
  // * returns true if result is a valid date/datetime
  if (!dtlIsInt($nWeeks)
    || (!dtlIsValidDate($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetDay($dts, dtlGetDay($dts) + (7 * $nWeeks))) {
    return $dts;
  }
  return FALSE;
}

function dtlAddDays($dts, $nDays) {
  // * returns $dts with $nDays added
  // * returns false if $dts is not a valid date or datetime
  //   or if result is not a valid date/datetime
  // * returns true if result is a valid date/datetime
  if (!dtlIsInt($nDays)
    || (!dtlIsValidDate($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetDay($dts, dtlGetDay($dts) + $nDays)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddHours($dts, $nHours) {
  // * returns $dts with $nHours added
  // * returns false if $dts is not a valid time/datetime
  //   or if result is not a valid time/datetime
  // * returns true if result is a valid time/datetime
  if (!dtlIsInt($nHours)
    || (!dtlIsValidTime($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetHours($dts, dtlGetHours($dts) + $nHours)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddMinutes($dts, $nMinutes) {
  // * returns $dts with $nMinutes added
  // * returns false if $dts is not a valid time/datetime
  //   or if result is not a valid time/datetime
  // * returns true if result is a valid time/datetime
  if (!dtlIsInt($nMinutes)
    || (!dtlIsValidTime($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetMinutes($dts, dtlGetMinutes($dts) + $nMinutes)) {
    return $dts;
  }
  return FALSE;
}

function dtlAddSeconds(&$dts, $nSeconds) {
  // * returns $dts with $nSeconds added
  // * returns false if $dts is not a valid time or datetime
  //   or if result is not a valid time or datetime
  // * returns true if result is a valid time or datetime
  if (!dtlIsInt($nSeconds)
    || (!dtlIsValidTime($dts)
      && !dtlIsValidDateTime($dts))
  ) {
    return FALSE;
  }
  if (dtlSetSeconds($dts, dtlGetSeconds($dts) + $nSeconds)) {
    return $dts;
  }
  return FALSE;
}

///////////////////////////////////////////////////////////////////////////
// functions to subtract dates and times

function dtlDateDiff($ds1, $ds2) {
  // * returns the number of days between 2 dates
  // * result will be +ve if $ds1 < $ds2
  // * result will be -ve if $ds1 > $ds2
  // * returns false if invalid dates supplied
  if (!dtlIsValidDate($ds1) || !dtlIsValidDate($ds2)) {
    return FALSE;
  }
  return dtlDateToMJD($ds2) - dtlDateToMJD($ds1);
}

function dtlTimeDiff($ts1, $ts2) {
  // * returns the difference in seconds between 2 times
  // * result will be +ve if $ts1 < $ts2
  // * result will be -ve if $ts1 > $ts2
  // * returns false if invalid times supplied
  if (!dtlIsValidTime($ts1) || !dtlIsValidTime($ts2)) {
    return FALSE;
  }
  return dtlTimeStrToSeconds($ts2) - dtlTimeStrToSeconds($ts1);
}

function dtlDateTimeDiff($dts1, $dts2) {
  // * returns the difference in days (float) between 2 datetimes
  // * result will be +ve if $ts1 < $ts2
  // * result will be -ve if $ts1 > $ts2
  // * returns false if invalid times supplied
  if (!dtlIsValidDateTime($dts1) || !dtlIsValidDateTime($dts2)) {
    return FALSE;
  }
  return dtlDateTimeToMJD($dts2) - dtlDateTimeToMJD($dts1);
}

///////////////////////////////////////////////////////////////////////////
// comparison functions

function dtlIsEarlierThan($dts1, $dts2) {
  // * compares two dates, datetimes, or times
  // * parameters must be the same type of string
  // * returns null for invalid input
  if ((dtlIsValidDate($dts1) && dtlIsValidDate($dts2))
    || (dtlIsValidTime($dts1)
      && dtlIsValidTime($dts2))
    || (dtlIsValidDateTime($dts1) && dtlIsValidDateTime($dts2))
  ) {
    return $dts1 < $dts2;
  }
  return NULL;
}

function dtlIsLaterThan($dts1, $dts2) {
  // * compares two dates, datetimes, or times
  // * parameters must be the same type of string
  // * returns null for invalid input
  if ((dtlIsValidDate($dts1) && dtlIsValidDate($dts2))
    || (dtlIsValidTime($dts1)
      && dtlIsValidTime($dts2))
    || (dtlIsValidDateTime($dts1) && dtlIsValidDateTime($dts2))
  ) {
    return $dts1 > $dts2;
  }
  return NULL;
}

///////////////////////////////////////////////////////////////////////////
// language functions:
// * currently 14 languages supported
// * language codes are 2-char ISO-639 codes.
// EN = English
// ES = Spanish
// PT = Portugese
// DE = German
// FR = French
// IT = Italian
// NL = Dutch
// ZH = Chinese
// RU = Russian
// JA = Japanese
// KO = Korean
// AR = Arabic
// IW = Hebrew
// TR = Turkish

$dtlLanguages = [];

// English:
$dtlLanguages['EN'] = [];
$dtlLanguages['EN']['monthNames'] = [
  1 => 'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];
$dtlLanguages['EN']['dayNames'] = [
  1 => 'Monday',
  'Tuesday',
  'Wednesday',
  'Thursday',
  'Friday',
  'Saturday',
  'Sunday',
];
// initialize language to English:
$dtlCurrentLanguage = 'EN';

function dtlIsSupportedLanguage($languageCode) {
  global $dtlLanguages;
  return array_key_exists($languageCode, $dtlLanguages);
}

function dtlSetLanguage($languageCode) {
  // * sets the current language according to $languageCode
  // * $languageCode case-insensitive
  global $dtlCurrentLanguage;
  $dtlCurrentLanguage = strtoupper($languageCode);
}

function dtlNormalizeLanguageCode(&$languageCode) {
  // * a simple support function to convert $languageCode from external to internal format
  if ($languageCode == '') {
    global $dtlCurrentLanguage;
    $languageCode = $dtlCurrentLanguage;
  }
  else {
    $languageCode = strtoupper($languageCode);
  }
}

function dtlAddLanguage($languageCode, $monthNames, $dayNames,
  $abbrevDayNames = 0) {
  // * adds support for a new language
  // * $languageCode can be anything, doesn't necessarily have to be an ISO-639 code
  //   (although that would be more consistent)
  // * $languageCode is case-insensitive - internally converted to uppercase
  //   NOTE this means that you can't have en and EN as seperate language codes.
  // * $monthNames must be an array with keys 1..12 containing month names in the new language
  // * $dayNames must be an array with keys 1..7 containing day names in the new language
  // NOTE if $languageCode is already in $dtlCurrentLanguages then the month and day names
  // for that language will be replaced with $monthNames and $dayNames
  // check $monthNames
  if (!is_array($monthNames) || count($monthNames) != 12
    || !isset($monthNames[1])
    || !isset($monthNames[2])
    || !isset($monthNames[3])
    || !isset($monthNames[4])
    || !isset($monthNames[5])
    || !isset($monthNames[6])
    || !isset($monthNames[7])
    || !isset($monthNames[8])
    || !isset($monthNames[9])
    || !isset($monthNames[10])
    || !isset($monthNames[11])
    || !isset($monthNames[12])
  ) {
    return FALSE;
  }
  // check $dayNames
  if (!is_array($dayNames) || count($dayNames) != 7 || !isset($dayNames[1])
    || !isset($dayNames[2])
    || !isset($dayNames[3])
    || !isset($dayNames[4])
    || !isset($dayNames[5])
    || !isset($dayNames[6])
    || !isset($dayNames[7])
  ) {
    return FALSE;
  }
  // check $abbrevDayNames
  if ((!dtlIsInt($abbrevDayNames) || $abbrevDayNames < 0)
    && (!is_array($abbrevDayNames) || count($abbrevDayNames) != 7
      || !isset($abbrevDayNames[1])
      || !isset($abbrevDayNames[2])
      || !isset($abbrevDayNames[3])
      || !isset($abbrevDayNames[4])
      || !isset($abbrevDayNames[5])
      || !isset($abbrevDayNames[6])
      || !isset($abbrevDayNames[7]))
  ) {
    return FALSE;
  }
  // parameters look ok, let's include them in the languages array:
  global $dtlLanguages;
  dtlNormalizeLanguageCode($languageCode);
  $dtlLanguages[$languageCode] = [];
  $dtlLanguages[$languageCode]['monthNames'] = $monthNames;
  $dtlLanguages[$languageCode]['dayNames'] = $dayNames;
  if ($abbrevDayNames != 0) {
    $dtlLanguages[$languageCode]['abbrevDayNames'] = $abbrevDayNames;
  }
}

function dtlMonthNames($languageCode = '') {
  // * returns array of month names in the language specified by $languageCode,
  //   or in the current language if $lanuageCode not specified
  // check language code:
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }
  global $dtlLanguages;
  return $dtlLanguages[$languageCode]['monthNames'];
}

function dtlMonthName($month, $languageCode = '') {
  // * returns the name of month $month (1..12) in the language designated by $languageCode
  // * if $languageCode not specified then default language code is used
  // * returns false if invalid language code or month number
  if ($month < 1 || $month > 12) {
    return FALSE;
  }
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }

  global $dtlLanguages;
  return $dtlLanguages[$languageCode]['monthNames'][$month];
}

function dtlAbbrevMonthName($month, $languageCode = '', $nChars = 3) {
  // * returns abbreviated month name
  // * returns false if invalid language code or month number or if $nChars not +ve
  if (!dtlIsInt($month) || $month < 0 || $month > 12 || !dtlIsInt($nChars)
    || $nChars <= 0
  ) {
    return FALSE;
  }
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }

  global $dtlLanguages;
  $abbrevMonthNames = isset($dtlLanguages[$languageCode]['abbrevMonthNames'])
    ? $dtlLanguages[$languageCode]['abbrevMonthNames'] : NULL;
  if (!isset($abbrevMonthNames)) {
    return dtlAbbrev(dtlMonthName($month, $languageCode), $nChars);
  }
  else {
    if (is_int($abbrevMonthNames)) {
      return dtlAbbrev(dtlMonthName($month, $languageCode), $abbrevMonthNames);
    }
    else {
      if (is_array($abbrevMonthNames)) {
        return $abbrevMonthNames[$month];
      }
    }
  }
  return FALSE;
}

function dtlDayNames($languageCode = '') {
  // * returns array of day names in the language specified by $languageCode,
  //   or in the current language if $lanuageCode not specified
  // check language code:
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }
  // setup day names array that matches user's week settings:
  global $dtlLanguages;
  $result = [];
  for ($dow = 1; $dow <= 7; $dow++) {
    $result[$dow] = $dtlLanguages[$languageCode]['dayNames'][$dow];
  }
  return $result;
}

function dtlDayName($dow, $languageCode = '') {
  // * returns the name of the day in the language designated by $languageCode
  // * day of week specified by $dow, can be from 0..6 or 1..7 depending on week settings
  // * if $languageCode not specified then default language code is used
  // * returns false if invalid language code or day of week number
  if (!dtlIsInt($dow)) {
    return FALSE;
  }
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }
  global $dtlLanguages;
  return $dtlLanguages[$languageCode]['dayNames'][$dow];
}

function dtlAbbrevDayName($dow, $languageCode = '', $nChars = 0) {
  global $dtlLanguages;
  // * returns abbreviated day name
  // * returns false if invalid language code or day of week number or if nChars not +ve
  // * if an abbreviation is provided in $dtlAbbrevDayNames then $nChars is ignored
  if (!dtlIsInt($nChars) || $nChars < 0) {
    return FALSE;
  }
  dtlNormalizeLanguageCode($languageCode);
  if (!dtlIsSupportedLanguage($languageCode)) {
    return FALSE;
  }

  $abbrevDayNames = isset($dtlLanguages[$languageCode]['abbrevDayNames'])
    ? $dtlLanguages[$languageCode]['abbrevDayNames'] : NULL;

  if (is_array($abbrevDayNames)) {
    return $abbrevDayNames[$dow];
  }
  else {
    // if nChars is 0 then look for number of chars to use:
    if ($nChars == 0) {
      if (is_int($abbrevDayNames)) {
        $nChars = $abbrevDayNames;
      }
      else {
        $nChars = 3;
      }
    }
    return dtlAbbrev(dtlDayName($dow, $languageCode), $nChars);
  }
  return FALSE;
}

///////////////////////////////////////////////////////////////////////////
// datetime formatting functions:

// default to ISO formats for display:
$dtlDateFormat = "YYYY-MM-DD";
$dtlTimeFormat = "HH:mm:ss";
$dtlDateTimeFormat = "YYYY-MM-DD HH:mm:ss";

$dtlDateSelectorFormat = $dtlDateFormat;
$dtlTimeSelectorFormat = $dtlTimeFormat;
$dtlDateTimeSelectorFormat = $dtlDateTimeFormat;

function dtlSetDateFormat($format) {
  // set default date format:
  global $dtlDateFormat;
  $dtlDateFormat = $format;
}

function dtlSetTimeFormat($format) {
  // set default time format:
  global $dtlTimeFormat;
  $dtlTimeFormat = $format;
}

function dtlSetDateTimeFormat($format) {
  // set default datetime format:
  global $dtlDateTimeFormat;
  $dtlDateTimeFormat = $format;
}

// some support variables for dtlFormat:
// (have to declare them outside the main function because PHP has issues with variable scope in nested functions)
$dtlFormatCounter = 0;
$dtlFormatResult = '';
$dtlFormatReplaced = FALSE;

function dtlFormat($dts, $format = '', $mustBeValid = TRUE) {
  // * returns a formatted string representing a date, time, or datetime
  // * supports DateStrs, TimeStrs, and DateTimeStrs
  // * if $format not specified then the default is used, depending on the type of input
  // * if a token appears in the format string that does not match anything in $dts
  //   (e.g. if YYYY appears in $format but $dts is a TimeStr)
  //   then the token is not converted
  // * returns false if invalid input

  // supports the following mappings:
  // YYYY			=> 4-digit year (0000..9999)
  // YY			=> 2-digit year (00..99)

  // Q			=> quarter (1..4)

  // Month		=> month name in default case, e.g. January, enero
  // MONTH		=> month name in upper case, e.g. JANUARY, ENERO
  // month		=> month name in lower case, e.g. january, enero
  // Mon			=> dtlAbbreviated month name in default case, e.g. Jan, ene
  // MON			=> dtlAbbreviated month name in upper case, e.g. JAN, ENE
  // mon			=> dtlAbbreviated month name in lower case, e.g. jan, ene
  // MM			=> 2-digit month (01..12)
  // M			=> 1- or 2-digit month (1..12)

  // WW			=> 2-digit week number (00..52)
  // W			=> 1- or 2-digit week number (0..52)

  // DOY			=> day of year (1..366)

  // Dayofweek	=> day of week in default case, e.g. Sunday, domingo
  // DAYOFWEEK	=> day of week in upper case, e.g. SUNDAY, DOMINGO
  // dayofweek	=> day of week in lower case, e.g., sunday, domingo
  // Day			=> dtlAbbreviated day of week in default case, e.g. Sun, dom
  // DAY			=> dtlAbbreviated day of week in upper case, e.g. SUN, DOM
  // day			=> dtlAbbreviated day of week in lower case, e.g., sun, dom
  // d			=> numeric day of week, (0..6 or 1..7 depending on settings)

  // DD			=> 2-digit day of month (01..31)
  // Dth			=> 1- or 2-digit day of month with ordinal suffix, e.g. 3rd
  // D			=> 1- or 2-digit day of month (1..31)

  // HH			=> hours in 24-hour time, 2-digit
  // H			=> hours in 24-hour time, 1- or 2-digit
  // hh			=> hours in 12-hour time, 2-digit
  // h			=> hours in 12-hour time, 1- or 2-digit

  // mm			=> minutes, 2-digit
  // m			=> minutes, 1- or 2-digit

  // ss			=> seconds, 2-digit
  // s			=> seconds, 1- or 2-digit

  // xm			=> am or pm
  // x.m.			=> a.m. or p.m.
  // XM			=> AM or PM
  // X.M.			=> A.M. or P.M.

  // check for valid string:
  if ($mustBeValid) {
    if (dtlIsValidDate($dts)) {
      $type = 'date';
    }
    else {
      if (dtlIsValidTime($dts)) {
        $type = 'time';
      }
      else {
        if (dtlIsValidDateTime($dts)) {
          $type = 'datetime';
        }
        else {
          return FALSE;
        }
      }
    }
  }
  else {
    if (dtlIsDateStr($dts)) {
      $type = 'date';
    }
    else {
      if (dtlIsTimeStr($dts)) {
        $type = 'time';
      }
      else {
        if (dtlIsDateTimeStr($dts)) {
          $type = 'datetime';
        }
        else {
          return FALSE;
        }
      }
    }
  }

  // if format not specified, used default format:
  if ($format == '') {
    if ($type == 'date') {
      global $dtlDateFormat;
      $format = $dtlDateFormat;
    }
    else {
      if ($type == 'time') {
        global $dtlTimeFormat;
        $format = $dtlTimeFormat;
      }
      else {
        if ($type == 'datetime') {
          global $dtlDateTimeFormat;
          $format = $dtlDateTimeFormat;
        }
      }
    }
  }

  // support function for dtlFormat:
  if (!function_exists('dtlReplace')) {
    function dtlReplace($token, $str) {
      global $dtlFormatResult, $dtlFormatCounter, $dtlFormatReplaced;
      if ($str === FALSE) {
        $dtlFormatResult .= $token;
      }
      else {
        $dtlFormatResult .= $str;
      }
      $dtlFormatCounter += strlen($token);
      $dtlFormatReplaced = TRUE;
    }
  }

  global $dtlFormatResult, $dtlFormatCounter, $dtlFormatReplaced;

  $dtlFormatResult = '';
  $dtlFormatCounter = 0;
  $len = strlen($format);
  while ($dtlFormatCounter < $len) {
    $dtlFormatReplaced = FALSE;
    $ch = $format{$dtlFormatCounter};
    if ($ch == 'Y') {
      if (substr($format, $dtlFormatCounter, 4) == 'YYYY') {
        dtlReplace('YYYY', dtlGetYear($dts));
      }
      else {
        if (substr($format, $dtlFormatCounter, 2) == 'YY') {
          $year = dtlGetYear($dts);
          if ($year !== FALSE) {
            $year = dtlZeroPad($year % 100);
          }
          dtlReplace('YY', $year);
        }
      }
    }
    else {
      if ($ch == 'Q') {
        dtlReplace('Q', dtlGetQuarter($dts));
      }
      else {
        if ($ch == 'M') {
          if (substr($format, $dtlFormatCounter, 5) == 'MONTH') {
            $monthName = dtlGetMonthName($dts);
            if ($monthName !== FALSE) {
              $monthName = strtoupper($monthName);
            }
            dtlReplace('MONTH', $monthName);
          }
          else {
            if (substr($format, $dtlFormatCounter, 3) == 'MON') {
              $monthName = dtlGetAbbrevMonthName($dts);
              if ($monthName !== FALSE) {
                $monthName = strtoupper($monthName);
              }
              dtlReplace('MON', $monthName);
            }
            else {
              if (substr($format, $dtlFormatCounter, 5) == 'Month') {
                dtlReplace('Month', dtlGetMonthName($dts));
              }
              else {
                if (substr($format, $dtlFormatCounter, 3) == 'Mon') {
                  dtlReplace('Mon', dtlGetAbbrevMonthName($dts));
                }
                else {
                  if (substr($format, $dtlFormatCounter, 2) == 'MM') {
                    $month = dtlGetMonth($dts);
                    if ($month !== FALSE) {
                      $month = dtlZeroPad($month);
                    }
                    dtlReplace('MM', $month);
                  }
                  else // M
                  {
                    dtlReplace('M', dtlGetMonth($dts));
                  }
                }
              }
            }
          }
        }
        else {
          if ($ch == 'm') {
            if (substr($format, $dtlFormatCounter, 5) == 'month') {
              $monthName = dtlGetMonthName($dts);
              if ($monthName !== FALSE) {
                $monthName = strtolower($monthName);
              }
              dtlReplace('month', $monthName);
            }
            else {
              if (substr($format, $dtlFormatCounter, 3) == 'mon') {
                $monthName = dtlGetAbbrevMonthName($dts);
                if ($monthName !== FALSE) {
                  $monthName = strtolower($monthName);
                }
                dtlReplace('mon', $monthName);
              }
              else {
                if (substr($format, $dtlFormatCounter, 2) == 'mm') {
                  $month = dtlGetMinutes($dts);
                  if ($month !== FALSE) {
                    $month = dtlZeroPad($month);
                  }
                  dtlReplace('mm', $month);
                }
                else // m
                {
                  dtlReplace('m', dtlGetMinutes($dts));
                }
              }
            }
          }
          else {
            if ($ch == 'W') {
              if (substr($format, $dtlFormatCounter, 2) == 'WW') {
                $woy = dtlGetWeekOfYear($dts);
                if ($woy !== FALSE) {
                  $woy = dtlZeroPad($woy);
                }
                dtlReplace('WW', $woy);
              }
              else // W
              {
                dtlReplace('W', dtlGetWeekOfYear($dts));
              }
            }
            else {
              if ($ch == 'D') {
                if (substr($format, $dtlFormatCounter, 9) == 'DAYOFWEEK') {
                  $dowName = dtlGetDayName($dts);
                  if ($dowName !== FALSE) {
                    $dowName = strtoupper($dowName);
                  }
                  dtlReplace('DAYOFWEEK', $dowName);
                }
                else {
                  if (substr($format, $dtlFormatCounter, 3) == 'DAY') {
                    $dowName = dtlGetAbbrevDayName($dts);
                    if ($dowName !== FALSE) {
                      $dowName = strtoupper($dowName);
                    }
                    dtlReplace('DAY', $dowName);
                  }
                  else {
                    if (substr($format, $dtlFormatCounter, 9) == 'Dayofweek') {
                      dtlReplace('Dayofweek', dtlGetDayName($dts));
                    }
                    else {
                      if (substr($format, $dtlFormatCounter, 3) == 'Day') {
                        dtlReplace('Day', dtlGetAbbrevDayName($dts));
                      }
                      else {
                        if (substr($format, $dtlFormatCounter, 3) == 'DOY') {
                          dtlReplace('DOY', dtlGetDayOfYear($dts));
                        }
                        else {
                          if (substr($format, $dtlFormatCounter, 3) == 'Dth') {
                            $day = dtlGetDay($dts);
                            if ($day !== FALSE) {
                              $day = dtlOrdinalSuffix($day);
                            }
                            dtlReplace('Dth', $day);
                          }
                          else {
                            if (substr($format, $dtlFormatCounter, 2) == 'DD') {
                              $day = dtlGetDay($dts);
                              if ($day !== FALSE) {
                                $day = dtlZeroPad($day);
                              }
                              dtlReplace('DD', $day);
                            }
                            else // D
                            {
                              dtlReplace('D', dtlGetDay($dts));
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
              else {
                if ($ch == 'd') {
                  if (substr($format, $dtlFormatCounter, 9) == 'dayofweek') {
                    $dowName = dtlGetDayName($dts);
                    if ($dowName !== FALSE) {
                      $dowName = strtolower($dowName);
                    }
                    dtlReplace('dayofweek', $dowName);
                  }
                  else {
                    if (substr($format, $dtlFormatCounter, 3) == 'day') {
                      $dowName = dtlGetAbbrevDayName($dts);
                      if ($dowName !== FALSE) {
                        $dowName = strtolower($dowName);
                      }
                      dtlReplace('day', $dowName);
                    }
                    else // d
                    {
                      dtlReplace('d', dtlGetDayOfWeek($dts));
                    }
                  }
                }
                else {
                  if ($ch == 'H') {
                    if (substr($format, $dtlFormatCounter, 2) == 'HH') {
                      $hours = dtlGetHours($dts);
                      if ($hours !== FALSE) {
                        $hours = dtlZeroPad($hours);
                      }
                      dtlReplace('HH', $hours);
                    }
                    else // H
                    {
                      dtlReplace('H', dtlGetHours($dts));
                    }
                  }
                  else {
                    if ($ch == 'h') {
                      if (substr($format, $dtlFormatCounter, 2) == 'hh') {
                        $hours = dtlGetHours($dts);
                        if ($hours !== FALSE) {
                          dtl24HourTo12($hours, $xm);
                          $hours = dtlZeroPad($hours);
                        }
                        dtlReplace('hh', $hours);
                      }
                      else // h
                      {
                        $hours = dtlGetHours($dts);
                        if ($hours !== FALSE) {
                          dtl24HourTo12($hours, $xm);
                        }
                        dtlReplace('h', $hours);
                      }
                    }
                    else {
                      if ($ch == 's') {
                        if (substr($format, $dtlFormatCounter, 2) == 'ss') {
                          $seconds = dtlGetSeconds($dts);
                          if ($seconds !== FALSE) {
                            $seconds = dtlZeroPad($seconds);
                          }
                          dtlReplace('ss', $seconds);
                        }
                        else // s
                        {
                          dtlReplace('s', dtlGetSeconds($dts));
                        }
                      }
                      else {
                        if ($ch == 'x') {
                          if (substr($format, $dtlFormatCounter, 2) == 'xm') {
                            $hours = dtlGetHours($dts);
                            if ($hours !== FALSE) {
                              dtl24HourTo12($hours, $xm);
                              $xm = strtolower($xm);
                            }
                            else {
                              $xm = FALSE;
                            }
                            dtlReplace('xm', $xm);
                          }
                          else {
                            if (substr($format, $dtlFormatCounter, 4)
                              == 'x.m.'
                            ) {
                              $hours = dtlGetHours($dts);
                              if ($hours !== FALSE) {
                                dtl24HourTo12($hours, $xm);
                                $xm = strtolower($xm{0} . '.' . $xm{1} . '.');
                              }
                              else {
                                $xm = FALSE;
                              }
                              dtlReplace('x.m.', $xm);
                            }
                          }
                        }
                        else {
                          if ($ch == 'X') {
                            if (substr($format, $dtlFormatCounter, 2) == 'XM') {
                              $hours = dtlGetHours($dts);
                              if ($hours !== FALSE) {
                                dtl24HourTo12($hours, $xm);
                              }
                              else {
                                $xm = FALSE;
                              }
                              dtlReplace('XM', $xm);
                            }
                            else {
                              if (substr($format, $dtlFormatCounter, 4)
                                == 'X.M.'
                              ) {
                                if (($hours = dtlGetHours($dts)) !== FALSE) {
                                  dtl24HourTo12($hours, $xm);
                                  $xm = $xm{0} . '.' . $xm{1} . '.';
                                }
                                else {
                                  $xm = FALSE;
                                }
                                dtlReplace('X.M.', $xm);
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    // if no replacement made, just add the char to the result:
    if (!$dtlFormatReplaced) {
      $dtlFormatResult .= $ch;
      $dtlFormatCounter++;
    }
  }
  return $dtlFormatResult;
}

function dtlFormatYear($year) {
  return str_pad($year, 4, '0', STR_PAD_LEFT);
}

function dtlPrint($dts, $format = '') {
  // print a date, time or datetime using the current format:
  print(dtlFormat($dts, $format));
}

function dtlSetDateTimeFormatISO() {
  // * sets the datetime format to ISO-8601
  // * currently does not support time zones
  dtlSetDateTimeFormat('YYYY-MM-DDTHH:mm:ss');
}

///////////////////////////////////////////////////////////////////////////
// functions for finding the number of days in one or more months

function dtlDaysInMonth($year, $month, $proleptic = FALSE) {
  // * returns the number of days in the specified month
  // * if !$proleptic, returns false if $month not a valid full Gregorian month, i.e. from 1582-11
  if (!dtlIsInt($year) || !dtlIsInRange($month, 1, 12)) {
    return FALSE;
  }
  if (!$proleptic && ($year * 100 + $month) < 158211) {
    return FALSE;
  }
  // get days in the month:
  if (in_array($month, [1, 3, 5, 7, 8, 10, 12])) {
    return 31;
  }
  else {
    if (in_array($month, [4, 6, 9, 11])) {
      return 30;
    }
    else // $month == 2
    {
      return dtlIsLeapYear($year) ? 29 : 28;
    }
  }
}

function dtlDaysInMonths($year, $month, $proleptic = FALSE) {
  // * returns the number of days in $year up to the end of $month
  // * if !$proleptic, returns false if $year not a valid full Gregorian year, i.e. from 1583
  if (!dtlIsInt($year) || !dtlIsInRange($month, 1, 12)) {
    return FALSE;
  }
  if (!$proleptic && $year < 1583) {
    return FALSE;
  }
  // get days in months:
  $leapDay = dtlIsLeapYear($year) ? 1 : 0;
  switch ($month) {
    case 1:
      return 31;
    case 2:
      return 59 + $leapDay;
    case 3:
      return 90 + $leapDay;
    case 4:
      return 120 + $leapDay;
    case 5:
      return 151 + $leapDay;
    case 6:
      return 181 + $leapDay;
    case 7:
      return 212 + $leapDay;
    case 8:
      return 243 + $leapDay;
    case 9:
      return 273 + $leapDay;
    case 10:
      return 304 + $leapDay;
    case 11:
      return 334 + $leapDay;
    case 12:
      return 365 + $leapDay;
  }
}

///////////////////////////////////////////////////////////////////////////
// functions for finding the number of seconds in a specific minute, hour or day

function dtlHadLeapSecond($ds) {
  // * returns true if the given date had a leap second (23:59:60)
  return in_array($ds, [
    '1971-12-31',
    '1972-06-30',
    '1972-12-31',
    '1973-12-31',
    '1974-12-31',
    '1975-12-31',
    '1976-12-31',
    '1977-12-31',
    '1978-12-31',
    '1979-12-31',
    '1981-06-30',
    '1982-06-30',
    '1983-06-30',
    '1985-06-30',
    '1987-12-31',
    '1989-12-31',
    '1990-12-31',
    '1992-06-30',
    '1993-06-30',
    '1994-06-30',
    '1995-12-31',
    '1997-06-30',
    '1998-12-31',
  ]);
}

///////////////////////////////////////////////////////////////////////////
// handy miscellaneous date functions:

function dtlIsLeapYear($year, $proleptic = FALSE) {
  // * returns true if $year is a leap year otherwise false
  // * if !$proleptic, returns false if $year not valid Gregorian, i.e. from 1583
  //   (1582 not counted because not a full year and the Feb was in the Julian part)
  if (!dtlIsInt($year)) {
    return FALSE;
  }
  if (!$proleptic && $year < 1583) {
    return FALSE;
  }
  // a leap year?
  if ($year % 400 == 0) {
    return TRUE;
  }
  else {
    if ($year % 100 == 0) {
      return FALSE;
    }
    else {
      if ($year % 4 == 0) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }
}

function dtlDaysInYear($year) {
  // * returns the number of days in $year
  // * returns false if $year not valid Gregorian
  if (!dtlIsInt($year) || $year < 1583 || $year > 9999) {
    return FALSE;
  }
  return dtlIsLeapYear($year) ? 366 : 365;
}

function dtlFirstDayOfYear($mixed = '') {
  // * finds the date of the first day of the year containing the date or datetime $dts
  // * returns false if invalid input
  // * if parameter not provided then assumes current date
  if ($mixed == '') {
    $year = dtlGetYear();
  }
  elseif (dtlIsInt($mixed)) {
    $year = $mixed;
  }
  else {
    if (dtlIsValidDate($mixed) || dtlIsValidDateTime($mixed)) {
      $year = dtlGetYear($mixed);
    }
    else {
      return FALSE;
    }
  }
  return dtlDateStr($year, 1, 1);
}

function dtlLastDayOfYear($mixed = '') {
  // * finds the date of the last day of the year containing the date or datetime $dts
  // * returns false if invalid input
  // * if parameter not provided then assumes current date
  if ($mixed == '') {
    $year = dtlGetYear();
  }
  else {
    if (dtlIsInt($mixed)) {
      $year = $mixed;
    }
    else {
      if (dtlIsValidDate($dts) || dtlIsValidDateTime($dts)) {
        $year = dtlGetYear($dts);
      }
      else {
        return FALSE;
      }
    }
  }
  return dtlDateStr($year, 12, 31);
}

function dtlFirstDayOfMonth($dts = '') {
  // * finds the date of the first day of the month containing the date or datetime $dts
  // * returns false if invalid input
  // * if parameter not provided then assumes current date
  if ($dts == '') {
    $ds = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
    else {
      $ds = dtlGetDate($dts);
    }
  }
  dtlSetDay($ds, 1);
  return $ds;
}

function dtlLastDayOfMonth($dts = '') {
  // * finds the date of the last day of the month containing the date or datetime $dts
  // * returns false if invalid input
  // * if parameter not provided then assumes current date
  if ($dts == '') {
    $ds = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
    else {
      $ds = dtlGetDate($dts);
    }
  }
  $da = dtlDateStrToArray($ds);
  dtlSetDay($ds, dtlDaysInMonth($da['year'], $da['month']));
  return $ds;
}

function dtlFirstDayOfWeek($mult = '', $woy = 0) {
  // 2 possible uses:
  // * if $mult is a date or datetime string, calculates the date of the first day of the week
  //   containing that date or datetime
  // * if $mult is a year, returns the date of the first day of week $woy in year $year
  // * if no params provided, find the first day of the current week
  // * returns false if invalid input or if a valid date cannot be found
  if ($mult == '') {
    $mult = dtlToday();
  }
  if (dtlIsValidDate($mult) || dtlIsValidDateTime($mult)) {
    $ds = dtlGetDate($mult);
    dtlSetDay($ds, dtlGetDay($ds) - dtlGetDayOfWeek($ds));
    return $ds;
  }
  else {
    if (dtlIsInRange($mult, 0, 9999) && dtlIsInRange($woy, 0, 53)) {
      // $mult is the year, we expect $woy is set as well:
      $year = $mult;
      // get day of week for first day of year (1..7):
      $Jan1 = "$year-01-01";
      $dowJan1 = dtlGetDayOfWeek($Jan1) + 1;
      $month = 1;
      $day = 2 - $dowJan1 + (($woy - 1) * 7);
      if ($dowJan1 > 4) {
        $day += 7;
      }
      dtlMakeDateValid($year, $month, $day);
      return dtlDateStr($year, $month, $day);
    }
  }
  // invalid input:
  return FALSE;
}

function dtlLastDayOfWeek($dts) {
  // * calculates the date of the last day of the week containing the date or datetime $dts:
  // * returns false if invalid input or if a valid date cannot be found
  if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  $ds = dtlGetDate($dts);
  dtlSetDay($ds, dtlGetDay($ds) - dtlGetDayOfWeek($ds) + 6);
  return $ds;
}

function dtlSunday($dts) {
  // * gets the date of the Sunday before or equal to the given date:
  // * returns false if invalid input or if a valid date cannot be found
  if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  $ds = dtlGetDate($dts);
  $dow = dtlGetDayOfWeek($ds);
  if (dtlSetDayOfWeek($ds, $dow)) {
    return $ds;
  }
  return FALSE;
}

// @todo needs test and debug (like most of the functions in this library!)
function dtlMonday($dts = '') {
  // * gets the date of the Monday before or equal to the given date:
  // * returns false if invalid input or if a valid date cannot be found
  // * if $dts not supplied then current datetime used
  if ($dts == '') {
    $ds = dtlToday();
  }
  else {
    if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
      return FALSE;
    }
    else {
      $ds = dtlGetDate($dts);
    }
  }
  $dow = dtlGetDayOfWeek($ds);
  if ($dow == DTL_MONDAY) {
    return $ds;
  }
  else {
    if ($dow == DTL_SUNDAY) {
      return dtlAddDays($ds, -6);
    }
  }
  $Sunday = dtlSunday($ds);
  return dtlAddDays($Sunday, 1);
}

function dtlNthWeekdayOfMonth($n, $weekday, $month, $year) {
  // * returns the datetime of the $nth $weekday in $month, $year,
  //   e.g. the 4th Thursday in November, 2002
  // start at the min date it could be:
  $day = $n * 7 - 6;
  $ds = dtlDateStr($year, $month, $day);
  // get the day of the week for that day:
  $dow = dtlGetDayOfWeek($ds);
  // calculate the number of days difference:
  $diff = ($weekday + 7 - $dow) % 7;
  // add the number of days to the start date:
  return dtlValidDateStr($year, $month, $day + $diff);
}

function dtlIsWeekend($dts) {
  // * returns true if the date or datetime $dts is on a Saturday or Sunday
  // * returns false if invalid input
  if (!dtlIsValidDate($dts) && !dtlIsValidDateTime($dts)) {
    return FALSE;
  }
  $dowName = dtlGetDayName($dts, 'EN');
  return $dowName == 'Sunday' || $dowName == 'Saturday';
}

function dtlIsBetween($dt, $dt1, $dt2) {
  return $dt >= $dt1 && $dt <= $dt2;
}

function dtlGetBirthsign($dt) {
  $month = dtlGetMonth($dt);
  $day = dtlGetDay($dt);
  if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 20)) {
    return "Aries";
  }
  if (($month == 4 && $day >= 21) || ($month == 5 && $day <= 21)) {
    return "Taurus";
  }
  if (($month == 5 && $day >= 22) || ($month == 6 && $day <= 21)) {
    return "Gemini";
  }
  if (($month == 6 && $day >= 22) || ($month == 7 && $day <= 23)) {
    return "Cancer";
  }
  if (($month == 7 && $day >= 24) || ($month == 8 && $day <= 23)) {
    return "Leo";
  }
  if (($month == 8 && $day >= 24) || ($month == 9 && $day <= 23)) {
    return "Virgo";
  }
  if (($month == 9 && $day >= 24) || ($month == 10 && $day <= 23)) {
    return "Libra";
  }
  if (($month == 10 && $day >= 24) || ($month == 11 && $day <= 22)) {
    return "Scorpio";
  }
  if (($month == 11 && $day >= 23) || ($month == 12 && $day <= 21)) {
    return "Sagittarius";
  }
  if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 20)) {
    return "Capricorn";
  }
  if (($month == 1 && $day >= 21) || ($month == 2 && $day <= 19)) {
    return "Aquarius";
  }
  if (($month == 2 && $day >= 20) || ($month == 3 && $day <= 20)) {
    return "Pisces";
  }
  return FALSE;
}

function dtlDateFromDayOfYear($year, $doy, $proleptic = FALSE) {
  // * given a year and the day of year, returns a DateStr
  // * returns false for invalid year or if the resulting date is invalid
  if (!dtlIsValidYear($year, $proleptic)) {
    return FALSE;
  }
  $month = 12;
  $monthFound = FALSE;
  while (!$monthFound) {
    $daysInMonths = dtlDaysInMonths($year, $month - 1, TRUE);
    if ($doy <= $daysInMonths) {
      $month--;
    }
    else {
      $monthFound = TRUE;
    }
  }
  $day = $doy - $daysInMonths;
  return dtlDateStr($year, $month, $day);
}

function dtl24HourTo12(&$hours, &$xm) {
  // * converts $hours from 24-hour format (0..23) to 12-hour format (1..12 AM or PM)
  // * sets $xm to 'AM' or 'PM'
  // * returns false if $hours is out of range
  if (!dtlIsInt($hours) || $hours < 0 || $hours > 23) {
    return FALSE;
  }
  $xm = $hours < 12 ? 'AM' : 'PM';
  if ($hours == 0) {
    $hours = 12;
  }
  else {
    if ($hours > 12) {
      $hours -= 12;
    }
  }
}

function dtl12HourTo24(&$hours, $xm) {
  // * converts $hours from 12-hour format (1..12 AM or PM) to 24-hour format (0..23)
  // * $xm must be set to 'AM' or 'PM' or 'am' or 'pm'
  // * returns false if $hours is out of range or if $xm not valid
  $xm = strtoupper($xm);
  if (!dtlIsInt($hours) || $hours < 1 || $hours > 12
    || ($xm != 'AM'
      && $xm != 'PM')
  ) {
    return FALSE;
  }
  if ($hours == 12 && $xm == 'AM') {
    $hours = 0;
  }
  else {
    if ($hours < 12 && $xm == 'PM') {
      $hours += 12;
    }
  }
}

///////////////////////////////////////////////////////////////////////////
// functions to find dates of holidays

function dtlChristmas($year = 0) {
  if ($year == 0) {
    $year = dtlGetYear();
  }
  elseif (!dtlIsValidYear($year)) {
    return FALSE;
  }
  return dtlDateStr($year, 12, 25);
}

function dtlEaster($year = 0) {
  // * returns date of Easter Sunday in the given $year
  // * returns false if invalid year input
  // * if $year == 0, uses current year
  // code got from here: http://aa.usno.navy.mil/faq/docs/easter.html
  // Algorithm by J.-M. Oudin (1940) and is reprinted in the
  // Explanatory Supplement to the Astronomical Almanac,
  // ed. P. K. Seidelmann (1992). See Chapter 12, "Calendars", by L. E. Doggett.
  if ($year == 0) {
    $year = dtlGetYear();
  }
  elseif (!dtlIsValidYear($year)) {
    return FALSE;
  }
  $c = dtlIntDiv($year, 100);
  $n = $year - 19 * dtlIntDiv($year, 19);
  $k = dtlIntDiv($c - 17, 25);
  $i = $c - dtlIntDiv($c, 4) - dtlIntDiv($c - $k, 3) + 19 * $n + 15;
  $i = $i - 30 * dtlIntDiv($i, 30);
  $i = $i - dtlIntDiv($i, 28) * (1 - dtlIntDiv($i, 28) * dtlIntDiv(29, $i + 1)
      * dtlIntDiv(21 - $n, 11));
  $j = $year + dtlIntDiv($year, 4) + $i + 2 - $c + dtlIntDiv($c, 4);
  $j = $j - 7 * dtlIntDiv($j, 7);
  $l = $i - $j;
  $month = 3 + dtlIntDiv($l + 40, 44);
  $day = $l + 28 - 31 * dtlIntDiv($month, 4);
  return dtlDateStr($year, $month, $day);
}

function dtlThanksgiving($year = NULL) {
  // * returns the date of Thanksgiving in the given $year
  // * returns false if invalid year input
  // NOTE - this function is at present only accurate beyond a certain date.
  // For many years Thanksgiving was simply when the President said it was,
  // i.e. it was determined by rule, not by a rule :)
  // Thanksgiving is now the 4th Thursday in November:
  if ($year == NULL) {
    $year = dtlGetYear();
  }
  elseif (!dtlIsValidYear($year)) {
    return FALSE;
  }
  return dtlNthWeekdayOfMonth(4, DTL_THURSDAY, 11, $year);
}

/**
 * This reverses the fields in a date string, i.e. converts a DD-MM-YYYY date
 * to YYYY-MM-DD or vice-versa.
 *
 * @param string $date
 */
function dtlReverseDate($ds) {
  $fields = explode('-', $ds);
  if (count($fields) != 3) {
    return FALSE;
  }
  return $fields[2] . '-' . $fields[1] . '-' . $fields[0];
}
