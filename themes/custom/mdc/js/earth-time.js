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
// Helper functions.

/**
 * Returns true if year is a leap year, otherwise false.
 * Returns undefined for invalid input.
 *
 * @requires mod()
 * @see misc.js
 *
 * @param {number} year
 * @returns {boolean}
 */
function isLeapYear(year) {
  if (isNaN(year)) {
    return undefined;
  }
  year = Math.floor(year);
  return (Math.mod(year, 400) == 0) || ((Math.mod(year, 4) == 0) && (Math.mod(year, 100) != 0));
}

/**
 * Returns the number of days in the specified month.
 * Returns undefined for invalid input.
 *
 * @param {number} year
 * @param {number} month
 * @returns {number}
 */
function daysInMonth(year, month) {
  // Check for valid input.
  if (isNaN(month)) {
    return undefined;
  }
  month = Math.floor(month);
  if (month < 1 || month > 12) {
    return undefined;
  }

  if (month == 2) {
    if (isNaN(year)) {
      return undefined;
    }
    return isLeapYear(year) ? 29 : 28;
  }
  if (month == 4 || month == 6 || month == 9 || month == 11) {
    return 30;
  }
  return 31;
}

/**
 * Returns the number of days in a given year.
 *
 * @param {number} year
 * @return int
 */
function daysInYear(year) {
  year = parseInt(year, 10);
  if (isNaN(year) || year < 0) {
    return undefined;
  }
  return isLeapYear(year) ? 366 : 365;
}

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
 * Uses ISO 8601 numbering.
 * @see Date.prototype.getDayOfWeek() (below)
 *
 * @var {array}
 */
var GREGORIAN_DAY_NAMES = [
  undefined,
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday"
];

/**
 * Returns the day name given the weekday number (1..7).
 * Uses ISO 8601 numbering.
 * @see Date.prototype.getDayOfWeek() (below)
 *
 * @param {int} dayOfWeek
 * @param {boolean} abbrev
 * @returns {string}
 */
function gregorianDayName(dayOfWeek, abbrev) {
  var name = GREGORIAN_DAY_NAMES[dayOfWeek];
  return abbrev ? name.substr(0, 3) : name;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Formatting functions.

/**
 * Format hour, minute, and second in HH:mm:ss format.
 *
 * @param {int} hour
 * @param {int} minute
 * @param {int} second
 * @returns {string}
 */
function formatHms(hour, minute, second) {
  return padDigits(hour, 2) + ':' + padDigits(minute, 2) + ':' + padDigits(second, 2);
}

/**
 * Given a datetime, format the time part HH:mm:ss.
 *
 * @param time
 * @return {string}
 */
function formatEarthTime(time) {
  var hour, minute, second;

  if (time instanceof Date) {
    hour = time.getHours();
    minute = time.getMinutes();
    second = time.getSeconds();
  }
  else if (typeof time == 'string') {
    var parts = time.split(':');

    // Get the hour.
    if (parts[0] !== undefined) {
      hour = parseInt(parts[0], 10);
      if (isNaN(hour) || hour < 0) {
        hour = 0;
      }
      else if (hour > 23) {
        hour = 23;
      }
    }
    else {
      hour = 0;
    }

    // Get the minute.
    if (parts[1] !== undefined) {
      minute = parseInt(parts[1], 10);
      if (isNaN(minute) || minute < 0) {
        minute = 0;
      }
      else if (minute > 59) {
        minute = 59;
      }
    }
    else {
      minute = 0;
    }

    // Get the second.
    if (parts[2] !== undefined) {
      second = parseInt(parts[2], 10);
      if (isNaN(second) || second < 0) {
        second = 0;
      }
      else if (second > 59) {
        second = 59;
      }
    }
    else {
      second = 0;
    }
  }
  else {
    // Not a Date or a string.
    return '00:00:00';
  }

  return formatHms(hour, minute, second);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Extend the Date object.

/**
 * Returns the day of the year (0..366), according to the system time zone.
 *
 * @returns {number}
 */
Date.prototype.getDayOfYear = function () {
  var dtYearStart = new Date(2000, 0, 1, 0, 0, 0, 0);
  dtYearStart.setFullYear(this.getFullYear());
  return Math.floor((this - dtYearStart) / MS_PER_DAY) + 1;
};

/**
 * Returns the day of the year (0..366), according to UTC.
 *
 * @returns {number}
 */
Date.prototype.getUTCDayOfYear = function () {
  var ts = Date.UTC(2000, 0, 1, 0, 0, 0, 0);
  var dtYearStart = new Date(ts);
  dtYearStart.setUTCFullYear(this.getUTCFullYear());
  return Math.floor((this - dtYearStart) / MS_PER_DAY) + 1;
};

/**
 * Returns the day of the week (1..7), according to UTC.
 *
 * Uses ISO 8601 numbering.
 * Javascript specifies Sunday = 0, Monday = 1 ... Saturday = 6.
 * ISO 8601 specifies Monday = 1 ... Saturday = 6, Sunday = 7.
 *
 * @returns {number}
 */
Date.prototype.getUTCDayOfWeek = function () {
  var dow = this.getUTCDay();
  return dow ? dow : 7;
};

/**
 * Returns the day of the week (1..7), according to system time zone.
 *
 * Uses ISO 8601 numbering, as above.
 *
 * @returns {number}
 */
Date.prototype.getDayOfWeek = function () {
  var dow = this.getDay();
  return dow ? dow : 7;
};
