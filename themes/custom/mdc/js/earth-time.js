/* Required files:
 Toolkit.js
 */

var SECONDS_PER_MINUTE = 60;
var MINUTES_PER_HOUR = 60;
var SECONDS_PER_HOUR = 3600;
var HOURS_PER_DAY = 24;
var SECONDS_PER_DAY = 86400;
var DAYS_PER_YEAR = 365.242199;
var SECONDS_PER_YEAR = SECONDS_PER_DAY * DAYS_PER_YEAR;

// units for metric time:
var MILS_PER_DAY = 1000;
var BEATS_PER_MIL = 100;

function EnglishMonth(nMonth) {
  switch (nMonth) {
    case 1:
      return "January";
    case 2:
      return "February";
    case 3:
      return "March";
    case 4:
      return "April";
    case 5:
      return "May";
    case 6:
      return "June";
    case 7:
      return "July";
    case 8:
      return "August";
    case 9:
      return "September";
    case 10:
      return "October";
    case 11:
      return "November";
    case 12:
      return "December";
  }
  return "?";
}

function AbbrevEnglishMonth(nMonth) {
  switch (nMonth) {
    case 1:
      return "Jan";
    case 2:
      return "Feb";
    case 3:
      return "Mar";
    case 4:
      return "Apr";
    case 5:
      return "May";
    case 6:
      return "Jun";
    case 7:
      return "Jul";
    case 8:
      return "Aug";
    case 9:
      return "Sep";
    case 10:
      return "Oct";
    case 11:
      return "Nov";
    case 12:
      return "Dec";
  }
  return "?";
}

function EnglishDayName(nDayOfWeek) {
  switch (nDayOfWeek) {
    case 0:
      return "Sunday";
    case 1:
      return "Monday";
    case 2:
      return "Tuesday";
    case 3:
      return "Wednesday";
    case 4:
      return "Thursday";
    case 5:
      return "Friday";
    case 6:
      return "Saturday";
  }
  return "?";
}

function FormatDate(date) {
  // returns a string representation of the date in the format YYYY-Month-DD
  return ThreeDigits(date.getUTCFullYear()) + " " + EnglishMonth(date.getUTCMonth() + 1) + " " + TwoDigits(date.getUTCDate());
  //	return ThreeDigits(date.getUTCFullYear()) + "-" + AbbrevEnglishMonth(date.getUTCMonth() + 1) + "-" + TwoDigits(date.getUTCDate());
}

function FormatTime(date) {
  // returns a string representation of the time part of date object in the format hh:mm:ss
  return TwoDigits(date.getUTCHours()) + ":" + TwoDigits(date.getUTCMinutes()) + ":" + TwoDigits(date.getUTCSeconds());
}

function FormatTimeDecimal(date) {
  // calculate fraction of a day:
  var hours = date.getUTCHours() + (date.getUTCMinutes() / MINUTES_PER_HOUR) + (date.getUTCSeconds() / SECONDS_PER_HOUR);
  var frac = (hours / HOURS_PER_DAY) * MILS_PER_DAY;
  var mils = Math.floor(frac);
  frac = (frac - mils) * BEATS_PER_MIL;
  var beats = Math.floor(frac);
  // returns a string representation of the time part of date object in the format 999.99
  return ThreeDigits(mils) + "." + TwoDigits(beats);
}
