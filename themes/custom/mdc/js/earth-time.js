/**
 * Created by Shaun Moss, 2017-04-29.
 * Functions for working with Gregorian dates and times.
 */

/**
 * Constants
 */
var MS_PER_SECOND = 1000;
var MS_PER_MINUTE = 60000;
var MS_PER_HOUR = 3600000;
var MS_PER_DAY = 86400000;

var SECONDS_PER_MINUTE = 60;
var SECONDS_PER_HOUR = 3600;
var SECONDS_PER_DAY = 86400;

var MINUTES_PER_HOUR = 60;
var MINUTES_PER_DAY = 1440;

var HOURS_PER_DAY = 24;

var DAYS_PER_WEEK = 7;
var DAYS_PER_YEAR = 365.2425;
var DAYS_PER_COMMON_YEAR = 365;
var DAYS_PER_LEAP_YEAR = 366;

var MONTHS_PER_YEAR = 12;

////////////////////////////////////////////////////////////////////////////////////////////////////
// Constants and functions for calendar month and day names.

/**
 * Names and abbreviated names of the Terran months.
 *
 * @var {array}
 */
var GREGORIAN_MONTH_NAMES = [
  undefined,
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December"
];

/**
 * Returns the month name given the month number (1..12).
 *
 * @param {int} month
 * @param {boolean} abbrev
 * @return {string}
 */
function gregorianMonthName(month, abbrev) {
  var name = GREGORIAN_MONTH_NAMES[month];
  return abbrev ? name.substr(0, 3) : name;
}

/**
 * Names and abbreviated names of the days of the week.
 *
 * @var {array}
 */
var GREGORIAN_DAY_NAMES = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday"
];

/**
 * Returns the day name given the day of the week number (0..6).
 *
 * @param {int} dayOfWeek
 * @param {boolean} abbrev
 * @returns {string}
 */
function gregorianDayName(dayOfWeek, abbrev) {
  var name = GREGORIAN_DAY_NAMES[dayOfWeek];
  return abbrev ? name.substr(0, 3) : name;
}
