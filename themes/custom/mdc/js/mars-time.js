/**
 * Created by shaun on 26/4/17.
 */

var MS_PER_SOL = 88775244.09;
var MS_PER_ZODE = 8877524.409;
var MS_PER_MIL = 88775.24409;
var MS_PER_TAL = 887.7524409;
var MS_PER_MICROSOL = 88.77524409;

var SOLS_PER_SHORT_WEEK = 6;
var SOLS_PER_LONG_WEEK = 7;
var SOLS_PER_SHORT_MONTH = 27;
var SOLS_PER_LONG_MONTH = 28;
var SOLS_PER_SHORT_QUARTER = 167;
var SOLS_PER_LONG_QUARTER = 168;
var SOLS_PER_SHORT_MIR = 668;
var SOLS_PER_LONG_MIR = 669;
var SOLS_PER_MIR = 668.591;
var SOLS_PER_KILOMIR = 668591;

var WEEKS_PER_MONTH = 4;
var WEEKS_PER_QUARTER = 24;
var WEEKS_PER_MIR = 96;

var MONTHS_PER_QUARTER = 6;
var MONTHS_PER_MIR = 24;

// Start datetime for Martian northern vernal equinox in 1609, the year Astronomy Novia was
// published by Johannes Kepler, and also the year the telescope was first used for astronomy, by
// Galileo Galilei. Expressed in milliseconds.
// 1609-Mar-10 18:00:40 (JD = 2308804.250463)
var EPOCH_START = Date.UTC(1609, 2, 10, 18, 0, 40);
// var EPOCH_START = Date.UTC(1609, 2, 11, 18, 40, 46, 400);

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
 * Returns number of sols in a given mir.
 *
 * @param {int} mir
 * @return int
 */
function solsInMir(mir) {
  return isLongMir(mir) ? SOLS_PER_LONG_MIR : SOLS_PER_SHORT_MIR;
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
 * Cache the function results.
 *
 * @type {int: object}
 */
var cache_timestamp2utopian = {};

/**
 * Convert a Unix timestemp to a Utopian datetime.
 *
 * @param {int} ts
 * @return {object}
 */
function timestamp2utopian(ts) {
  // If no parameter provided, use current time:
  if (ts === undefined) {
    ts = (new Date()).valueOf();
  }

  // Create utopianDateTime object.
  var utopianDateTime = {};

  // Convert the timestamp to number of sols since EPOCH_START.
  var sols = (ts - EPOCH_START) / MS_PER_SOL;
  // Round off to microsols.
  sols = Math.round(sols * 1e6) / 1e6;
  var origRem = Math.floor(sols);
  var rem = origRem;
  var time = Math.round((sols - rem) * 1e6) / 1e6;

  // Check the cache. We cache dates, not times.
  if (cache_timestamp2utopian[origRem] !== undefined) {
    utopianDateTime = cache_timestamp2utopian[origRem];
  }
  else {

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

    // Calculate sol of the month.
    // Add 1 because if there are 0 sols remaining we are in the first sol of the month.
    var sol = rem + 1;

    // Create the result object with the date.
    utopianDateTime.mir = mir;
    utopianDateTime.month = month;
    utopianDateTime.sol = sol;

    // Get the month and sol names.
    utopianDateTime.monthName = utopianMonthName(month);
    utopianDateTime.solName = utopianSolName(sol);

    // Cache the result.
    cache_timestamp2utopian[origRem] = utopianDateTime;
  }

  // Add the time and formatted time string.
  utopianDateTime.time = time;
  var microsols = Math.round(time * 1e6);
  var mils = Math.floor(microsols / 1000);
  microsols -= mils * 1000;

  return utopianDateTime;
}

/**
 * Convert a JS Date object to a Utopian date.
 *
 * @param {Date} date
 * @returns {object}
 */
function gregorian2utopian(date) {
  // If no parameter provided, use current datetime.
  if (date === undefined) {
    date = new Date();
  }
  return timestamp2utopian(date.valueOf());
}

/**
 * Counts all sols in mirs from 1 to m.
 * Note, does not count mir 0.
 *
 * @param {int} m
 * @returns {int}
 */
function solsInMirs(m) {
  var a = Math.floor((m + 1) / 2);
  var b = Math.floor(m / 10);
  var c = Math.floor(m / 100);
  var d = Math.floor(m / 1000);
  return (m * SOLS_PER_SHORT_MIR) + a + b - c + d;
}

/**
 * Convert a Utopian datetime object to a Unix timestamp.
 *
 * @param {object} utopianDateTime
 * @return {int}
 */
function utopian2timestamp(utopianDateTime) {
  // Convert Utopian datetime to sols:
  var sols = 0, n, q;
  var sols2 = 0, x;

  // Count how many sols from the start of the epoch to the start of the mir.
  if (utopianDateTime.mir > 0) {
    // Positive mir.
    sols = SOLS_PER_LONG_MIR + solsInMirs(utopianDateTime.mir - 1);

    // Double check.
    // for (x = 0; x < utopianDateTime.mir; x++) {
    //   sols2 += solsInMir(x);
    // }
  }
  else if (utopianDateTime.mir < 0) {
    // Negative mir.
    sols = -solsInMirs(-utopianDateTime.mir);

    // Double check.
    // for (x = utopianDateTime.mir; x < 0; x++) {
    //   sols2 -= solsInMir(x);
    // }
  }

  // Count the sols in all months before the current one.
  n = utopianDateTime.month - 1;
  q = Math.floor(n / MONTHS_PER_QUARTER);
  sols += (q * SOLS_PER_SHORT_QUARTER) + (n - (q * MONTHS_PER_QUARTER)) * SOLS_PER_LONG_MONTH;

  // Add the sols in the current month before the current one.
  sols += utopianDateTime.sol - 1;

  // Add the time of day.
  sols += utopianDateTime.time;

  // Convert to Unix timestamp.
  return EPOCH_START + (sols * MS_PER_SOL);
}

/**
 * Convert a Utopian datetime object to a JS Date object.
 *
 * @param {object} utopianDateTime
 * @return {int}
 */
function utopian2gregorian(utopianDateTime) {
  return new Date(utopian2timestamp(utopianDateTime));
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Constants and functions for calendar month and sol names.

// Names and abbreviated names of the Martian months.
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

// Names and abbreviated names of the Martian sols.
var UTOPIAN_SOL_NAMES = [
  "Sunsol",
  "Phobosol",
  "Earthsol",
  "Venusol",
  "Mercurisol",
  "Jupitersol",
  "Deimosol"
];

/**
 * Returns the sol name given the sol number (1..28).
 *
 * @param {int} nSolOfMonth
 * @param {boolean} abbrev
 * @returns {string}
 */
function utopianSolName(nSolOfMonth, abbrev) {
  var name = UTOPIAN_SOL_NAMES[(nSolOfMonth - 1) % 7];
  return abbrev ? name.substr(0, 1) : name;
}

function testUtopianConvert() {
  var u, ts, u2, ts2, result;

  var testDates = [
    {mir: 0, month: 1, sol: 1, time: 0},
    {mir: 1, month: 1, sol: 1, time: 0},
    {mir: 2, month: 1, sol: 1, time: 0},
    {mir: 9, month: 1, sol: 1, time: 0},
    {mir: 10, month: 1, sol: 1, time: 0},
    {mir: 11, month: 1, sol: 1, time: 0},
    {mir: 99, month: 1, sol: 1, time: 0},
    {mir: 100, month: 1, sol: 1, time: 0},
    {mir: 101, month: 1, sol: 1, time: 0},
    {mir: 999, month: 1, sol: 1, time: 0},
    {mir: 1000, month: 1, sol: 1, time: 0},
    {mir: 1001, month: 1, sol: 1, time: 0},
    {mir: -1, month: 1, sol: 1, time: 0},
    {mir: -2, month: 1, sol: 1, time: 0},
    {mir: -9, month: 1, sol: 1, time: 0},
    {mir: -10, month: 1, sol: 1, time: 0},
    {mir: -11, month: 1, sol: 1, time: 0},
    {mir: -99, month: 1, sol: 1, time: 0},
    {mir: -100, month: 1, sol: 1, time: 0},
    {mir: -101, month: 1, sol: 1, time: 0},
    {mir: -999, month: 1, sol: 1, time: 0},
    {mir: -1000, month: 1, sol: 1, time: 0},
    {mir: -1001, month: 1, sol: 1, time: 0},
    {mir: 0, month: 24, sol: 27, time: 0},
    {mir: 1, month: 24, sol: 27, time: 0},
    {mir: 2, month: 24, sol: 27, time: 0},
    {mir: 9, month: 24, sol: 27, time: 0},
    {mir: 10, month: 24, sol: 27, time: 0},
    {mir: 11, month: 24, sol: 27, time: 0},
    {mir: 99, month: 24, sol: 27, time: 0},
    {mir: 100, month: 24, sol: 27, time: 0},
    {mir: 101, month: 24, sol: 27, time: 0},
    {mir: 999, month: 24, sol: 27, time: 0},
    {mir: 1000, month: 24, sol: 27, time: 0},
    {mir: 1001, month: 24, sol: 27, time: 0},
    {mir: -1, month: 24, sol: 27, time: 0},
    {mir: -2, month: 24, sol: 27, time: 0},
    {mir: -9, month: 24, sol: 27, time: 0},
    {mir: -10, month: 24, sol: 27, time: 0},
    {mir: -11, month: 24, sol: 27, time: 0},
    {mir: -99, month: 24, sol: 27, time: 0},
    {mir: -100, month: 24, sol: 27, time: 0},
    {mir: -101, month: 24, sol: 27, time: 0},
    {mir: -999, month: 24, sol: 27, time: 0},
    {mir: -1000, month: 24, sol: 27, time: 0},
    {mir: -1001, month: 24, sol: 27, time: 0}
  ];

  var passCount = 0, failCount = 0;

  for (var i in testDates) {
    u = testDates[i];
    // u.time = 0.123456;
    console.log(u);
    ts = utopian2timestamp(u);
    u2 = timestamp2utopian(ts);
    ts2 = utopian2timestamp(u2);
    if (ts == ts2) {
      result = 'PASS';
      passCount++;
    }
    else {
      result = 'FAIL';
      failCount++;
    }
    console.log(result);
  }
  console.log('PASS: ' + passCount + ', FAIL: ' + failCount);
}
