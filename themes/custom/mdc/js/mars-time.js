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

  var kilomirs, mirLen;

  // Convert the timestamp to number of sols since EPOCH_START.
  var sols = (ts - EPOCH_START) / MS_PER_SOL;
  var rem = Math.floor(sols);
  var time = sols - rem;

  // Get the current kilomir.
  if (sols > 0) {
    kilomirs = Math.floor(rem / SOLS_PER_KILOMIR);
  }
  else if (sols < 0) {
    kilomirs = Math.ceil(rem / SOLS_PER_KILOMIR) - 1;
  }
  else { // sols == 0
    kilomirs = 0;
  }

  // Adjust so remainder is positive.
  rem -= kilomirs * SOLS_PER_KILOMIR;

  // Calculate the mir.
  var mir = kilomirs * 1000;
  while (true) {
    // If we have more sols left than there are in current mir, subtract the number of sols in the
    // current mir from the remainder, and go to the next mir.
    mirLen = solsInMir(mir);
    // Done?
    if (rem < mirLen) {
      break;
    }
    rem -= mirLen;
    mir++;
  }

  // Calculate the month.
  var month = 1, monthLen;
  // There are probably optimisations possible here by counting down quarters.
  // Worst case the loop runs 23 times.
  while (true) {
    // If we have more sols left than there are in current month, subtract the number of sols in the
    // current month from the remainder, and go to the next month.
    monthLen = solsInMonth(mir, month);
    // Done?
    if (rem <= monthLen) {
      break;
    }
    rem -= monthLen;
    month++;
  }

  // Calculate sol of the month.
  // Add 1 because if there are 0 sols remaining we are in the first sol of the month.
  var sol = rem + 1;

  // Calculate the time.
  var microsols = Math.round(time * 1e6);
  time = microsols / 1e6;
  if (time == 1) {
    // Round up.
    time = 0;
    if (sol == monthLen) {
      sol = 1;
      if (month == 24) {
        month = 1;
        mir++;
      }
      else {
        month++;
      }
    }
    else {
      sol++;
    }
  }

  // Create the result object.
  utopianDateTime.mir = mir;
  utopianDateTime.month = month;
  utopianDateTime.sol = sol;
  utopianDateTime.time = time;

  // Get the month and sol names.
  utopianDateTime.monthName = utopianMonthName(month);
  utopianDateTime.solName = utopianSolName(sol);

  // Formatted time string.
  var mils = Math.floor(microsols / 1000);
  microsols -= mils * 1000;
  utopianDateTime.timeStr = 'm' + padDigits(mils, 3) + '.' + padDigits(microsols, 3);

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
