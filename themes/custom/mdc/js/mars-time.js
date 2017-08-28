/**
 * Created by Shaun Moss on 2017-04-26.
 */

////////////////////////////////////////////////////////////////////////////////////////////////////
// Constants.

var SECONDS_PER_SOL = 88775.244147;
var SECONDS_PER_DAY = 86400;
var DAYS_PER_SOL = 1.0274912517;

var MS_PER_SOL = SECONDS_PER_SOL * 1000;
var MS_PER_ZODE = MS_PER_SOL / 10;
var MS_PER_MICROSOL = MS_PER_SOL / 1e6;

var SOLS_PER_SHORT_WEEK = 6;
var SOLS_PER_LONG_WEEK = 7;
var SOLS_PER_SHORT_MONTH = 27;
var SOLS_PER_LONG_MONTH = 28;
var SOLS_PER_SHORT_QUARTER = 167;
var SOLS_PER_LONG_QUARTER = 168;
var SOLS_PER_SHORT_MIR = 668;
var SOLS_PER_LONG_MIR = 669;
var SOLS_PER_MIR = 668.591;
var SOLS_PER_KILOMIR = SOLS_PER_MIR * 1000;

var WEEKS_PER_MONTH = 4;
var WEEKS_PER_QUARTER = 24;
var WEEKS_PER_MIR = 96;

var MONTHS_PER_QUARTER = 6;
var MONTHS_PER_MIR = 24;

////////////////////////////////////////////////////////////////////////////////////////////////////
// Epoch start datetime.

/**
 * Updated epoch start date! This is as close as I can get with current information.
 *
 * @see http://marscalendar.com/epoch
 */
var EPOCH_START = Date.UTC(1609, 2, 12, 19, 19, 6);

////////////////////////////////////////////////////////////////////////////////////////////////////
// Helper functions.

/**
 * Returns true if a long mir.
 *
 * @param {int} mir
 * @return {boolean}
 */
function isLongMir(mir) {
  // Rules:
  // - All odd years are long years.
  // - If the mir is divisible by 1000, then it's a long mir.
  // - If the mir is divisible by 100, then it's not a long mir.
  // - If the mir is divisible by 10, then it is a long mir.
  return (mir % 2 != 0) || (mir % 1000 == 0) || (mir % 100 != 0 && mir % 10 == 0);
}

/**
 * Returns number of sols in a given month.
 *
 * @param {int} mir
 * @param {int} month
 * @return int
 */
function solsInMonth(mir, month) {
  if (month == MONTHS_PER_MIR) {
    return isLongMir(mir) ? SOLS_PER_LONG_MONTH : SOLS_PER_SHORT_MONTH;
  }
  return (month % 6 == 0) ? SOLS_PER_SHORT_MONTH : SOLS_PER_LONG_MONTH;
}

/**
 * Returns number of sols in a given mir.
 *
 * @param {int} mir
 * @return int
 */
function solsInMir(mir) {
  return isLongMir(mir) ? SOLS_PER_LONG_MIR : SOLS_PER_SHORT_MIR;
}

/**
 * Counts the sols in mirs from 1 to mir (or -mir to -1).
 * Note: does not count mir 0.
 *
 * @param {int} mir
 * @returns {int}
 */
function solsInMirs(mir) {
  var a = Math.floor((mir + 1) / 2);
  var b = Math.floor(mir / 10);
  var c = Math.floor(mir / 100);
  var d = Math.floor(mir / 1000);
  return (mir * SOLS_PER_SHORT_MIR) + a + b - c + d;
}

/**
 * Calculate the sol of the mir (1..669).
 *
 * @param {int} month
 * @param {int} solOfMonth
 * @returns {int}
 */
function solOfMir(month, solOfMonth) {
  var q = Math.floor((month - 1) / 6);
  var m = month - (q * 6) - 1;
  return (q * SOLS_PER_SHORT_QUARTER) + (m * SOLS_PER_LONG_MONTH) + solOfMonth;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Convert between Unix timestamps and Mars datetimes.

/**
 * Convert a Unix timestamp to a Utopian datetime.
 *
 * @param {int} timestamp
 *   Unix timestamp.
 * @param {int} timeZone
 *   The time zone that the datetime should be relative to (-5..5).
 * @return {object}
 */
function timestamp2utopian(timestamp, timeZone) {
  // Create dtMars object.
  var dtMars = {};

  // Make the timestamp an integer (round off to milliseconds).
  timestamp = Math.round(timestamp);

  // Convert the timestamp to number of sols since EPOCH_START.
  var sols = (timestamp - EPOCH_START) / MS_PER_SOL;

  // Round off to microsols.
  sols = Math.round(sols * 1e6) / 1e6;

  // Adjust for time zone.
  if (timeZone === undefined) {
    timeZone = 0;
  }
  sols += timeZone / 10;

  // Get the remainder.
  var origRem = Math.floor(sols);
  var rem = origRem;

  // Calculate the kilomir.
  // - kilomir -1 is from -1000 to   -1
  // - Kilomir  0 is from     0 to  999
  // - Kilomir  1 is from  1000 to 1999
  var kilomir;
  if (rem > 0) {
    kilomir = Math.floor(rem / SOLS_PER_KILOMIR);
  }
  else if (rem < 0) {
    kilomir = Math.ceil(rem / SOLS_PER_KILOMIR) - 1;
  }
  else { // rem == 0
    kilomir = 0;
  }

  // Adjust so remainder is positive.
  rem -= kilomir * SOLS_PER_KILOMIR;

  // Calculate the mir.
  var mirs = Math.floor(rem / SOLS_PER_MIR);
  var mir = kilomir * 1000 + mirs;
  if (mirs > 0) {
    rem -= SOLS_PER_LONG_MIR + solsInMirs(mirs - 1);
  }
  var mirLen = solsInMir(mir);
  if (rem >= mirLen) {
    rem -= mirLen;
    mir++;
  }

  // Calculate the quarter (0..3).
  var month = 1, monthLen;
  var q = Math.floor(rem / SOLS_PER_SHORT_QUARTER);
  if (q == 4) {
    q = 3;
  }
  if (q > 0) {
    month += q * 6;
    rem -= q * SOLS_PER_SHORT_QUARTER;
  }

  // Calculate the month.
  var m = Math.floor(rem / SOLS_PER_LONG_MONTH);
  if (m > 0) {
    month += m;
    rem -= m * SOLS_PER_LONG_MONTH;
  }

  // Calculate sol of the month (1..28).
  // Add 1 because if there are 0 sols remaining we are in the first sol of the month.
  var sol = rem + 1;

  // Create the result object.
  dtMars.mir = mir;
  dtMars.month = month;
  dtMars.solOfMonth = sol;
  dtMars.monthName = utopianMonthName(month);
  dtMars.solOfWeek = (sol - 1) % SOLS_PER_LONG_WEEK + 1;
  dtMars.solName = utopianSolName(dtMars.solOfWeek);
  dtMars.solOfMir = solOfMir(month, sol);
  dtMars.timeZone = timeZone;

  // Get the mils.
  var microsols = Math.round((sols - origRem) * 1e6);
  dtMars.mils = microsols / 1e3;

  return dtMars;
}

/**
 * Convert a Utopian datetime object to a Unix timestamp.
 *
 * @param {object} dtMars
 * @return {int}
 */
function utopian2timestamp(dtMars) {
  // Convert Utopian datetime to sols:
  var sols = 0, n, q;
  var sols2 = 0, x;

  // Count how many sols from the start of the epoch to the start of the mir.
  if (dtMars.mir > 0) {
    // Positive mir.
    sols = SOLS_PER_LONG_MIR + solsInMirs(dtMars.mir - 1);

    // Double check.
    // for (x = 0; x < dtMars.mir; x++) {
    //   sols2 += solsInMir(x);
    // }
  }
  else if (dtMars.mir < 0) {
    // Negative mir.
    sols = -solsInMirs(-dtMars.mir);

    // Double check.
    // for (x = dtMars.mir; x < 0; x++) {
    //   sols2 -= solsInMir(x);
    // }
  }

  // Count the sols in all months before the current one.
  n = dtMars.month - 1;
  q = Math.floor(n / MONTHS_PER_QUARTER);
  sols += (q * SOLS_PER_SHORT_QUARTER) + (n - (q * MONTHS_PER_QUARTER)) * SOLS_PER_LONG_MONTH;

  // Add the sols in the current month before the current one.
  sols += dtMars.solOfMonth - 1;

  // Add the mils.
  sols += (dtMars.mils / 1e3);

  // Add time zone offset.
  if (dtMars.timeZone !== undefined) {
    sols -= dtMars.timeZone / 10;
  }

  // Convert to Unix timestamp.
  return Math.round(EPOCH_START + (sols * MS_PER_SOL));
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Convert between Earth and Mars datetimes.

/**
 * Convert a JS Date object to a Utopian datetime object.
 *
 * @param {Date} dtEarth
 * @returns {object}
 */
function gregorian2utopian(dtEarth, timeZone) {
  var timestamp = dtEarth.valueOf();
  return timestamp2utopian(timestamp, timeZone);
}

/**
 * Convert a Utopian datetime object to a JS Date object.
 *
 * @param {object} dtMars
 * @return {int}
 */
function utopian2gregorian(dtMars) {
  var timestamp = utopian2timestamp(dtMars);
  return new Date(timestamp);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Constants and functions for calendar month and sol names.

/**
 * Names and abbreviated names of the Martian months.
 *
 * @var {array}
 */
var UTOPIAN_MONTH_NAMES = [
  undefined,
  ["Phe", "Phoenix", "Phoenix", "00 55.91"],
  ["Cet", "Cetus", "Whale", "01 40.10"],
  ["Dor", "Dorado", "Dolphinfish", "05 14.51"],
  ["Lep", "Lepus", "Hare", "05 33.95"],
  ["Col", "Columba", "Dove", "05 51.76"],
  ["Mon", "Monoceros", "Unicorn", "07 03.63"],
  ["Vol", "Volans", "Flying Fish", "07 47.73"],
  ["Lyn", "Lynx", "Lynx", "07 59.53"],
  ["Cam", "Camelopardalis", "Giraffe", "08 51.37"],
  ["Cha", "Chamaeleon", "Chameleon", "10 41.53"],
  ["Hya", "Hydra", "Sea Serpent", "11 36.73"],
  ["Crv", "Corvus", "Raven", "12 26.52"],
  ["Cen", "Centaurus", "Centaur", "13 04.27"],
  ["Dra", "Draco", "Dragon", "15 08.64"],
  ["Lup", "Lupus", "Wolf", "15 13.21"],
  ["Aps", "Apus", "Bird of Paradise", "16 08.65"],
  ["Pav", "Pavo", "Peacock", "19 36.71"],
  ["Aql", "Aquila", "Eagle", "19 40.02"],
  ["Vul", "Vulpecula", "Fox", "20 13.88"],
  ["Cyg", "Cygnus", "Swan", "20 35.28"],
  ["Del", "Delphinus", "Dolphin", "20 41.61"],
  ["Gru", "Grus", "Crane", "22 27.39"],
  ["Peg", "Pegasus", "Pegasus", "22 41.84"],
  ["Tuc", "Tucana", "Toucan", "23 46.64"]
];

/**
 * Returns the month name given the month number (1..24).
 *
 * @param {int} month
 * @param {boolean} abbrev
 * @return {string}
 */
function utopianMonthName(month, abbrev) {
  return UTOPIAN_MONTH_NAMES[month][abbrev ? 0 : 1];
}

/**
 * Names and abbreviated names of the sols of the week.
 * Following ISO 8601, the week begins with Lunasol (Martian Monday) and ends with Sunsol.
 *
 * @var {array}
 */
var UTOPIAN_SOL_NAMES = [
  undefined,
  "Lunasol",
  "Earthsol",
  "Venusol",
  "Mercurisol",
  "Jupitersol",
  "Saturnsol",
  "Sunsol"
];

/**
 * Returns the sol name given the weeksol number (1..7).
 *
 * @param {int} weeksolNum
 * @param {boolean} abbrev
 * @returns {string}
 */
function utopianSolName(weeksolNum, abbrev) {
  var name = UTOPIAN_SOL_NAMES[weeksolNum];
  return abbrev ? name.substr(0, 1) : name;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Formatting functions.

/**
 * Given a Mars time in mils, format as 999.999.
 *
 * @param {number} mils
 * @return {string}
 */
function formatMarsTime(mils) {
  if (typeof mils == 'string') {
    mils = parseFloat(mils, 10);
    if (isNaN(mils)) {
      return '000.000';
    }
  }
  if (typeof mils != 'number') {
    return '000.000';
  }
  // mils is a number, check ranges.
  if (mils < 0) {
    return '000.000';
  }
  if (mils > 999.999) {
    return '999.999';
  }
  // Format the time.
  var wholeMils = Math.floor(mils);
  var microsols = Math.round((mils - wholeMils) * 1000);
  return padDigits(wholeMils, 3) + '.' + padDigits(microsols, 3);
}
