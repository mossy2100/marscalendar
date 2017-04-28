/**
 * Created by shaun on 26/4/17.
 */

// To convert Earth time to Mars.
var MS_PER_SOL = 88775244.09;
var SOLS_PER_KILOMIR = 668591;

// Start datetime for Martian northern vernal equinox in 1609, the year Astronomy Novia was
// published by Johannes Kepler, and also the year the telescope was first used for astronomy, by
// Galileo Galilei. Expressed in milliseconds.
// 1609-Mar-10 18:00:40 (JD = 2308804.250463)
var EPOCH_START_DATE = Date.UTC(1609, 2, 10, 18, 0, 40);

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
  return (mir % 2 == 1) || (mir % 1000 == 0) || (mir % 100 != 0 && mir % 10 == 0);
}

/**
 * Returns number of sols in a given mir.
 *
 * @param {int} mir
 * @return int
 */
function solsInMir(mir) {
  return isLongMir(mir) ? 669 : 668;
}

/**
 * Returns number of sols in a given month.
 *
 * @param {int} mir
 * @param {int} month
 * @return int
 */
function solsInMonth(mir, month) {
  return ((month == 24 && !isLongMir(mir)) || (month % 6 == 0)) ? 27 : 28;
}

/**
 * Calculate the Utopian datetime.
 *
 * Input: a JS Date object
 * Output: Object representing Mars date and time.
 *
 * Currently only supports positive mirs.
 * @todo Update to add support for negative mirs.
 *
 * @param {Date} date
 * @return object
 */
function gregorian2utopian(date) {
  // If no parameter provided, use current time:
  if (date === undefined) {
    date = new Date();
  }

  // Create utopianDate object.
  var utopianDate = {};

  // Convert the milliseconds to number of sols since MARS_START.
  var ms = date.valueOf();
  var sols = (ms - EPOCH_START_DATE) / MS_PER_SOL;
  var rem = Math.floor(sols);
  var time = sols - rem;

  // Get the current millennia.
  var millennnia = Math.floor(rem / SOLS_PER_KILOMIR);
  rem = rem % SOLS_PER_KILOMIR;

  // Calculate the mir.
  var mir = 0, mirLen;
  // There are probably optimisations possible here, by counting down centuries and decades.
  // Worst case the loop runs 999 times.
  while (true) {
    // If we have more sols left than there are in current mir, subtract the number of sols in the
    // current mir from the remainder, and go to the next mir.
    mirLen = solsInMir(mir);
    // Done?
    if (rem <= mirLen) {
      break;
    }
    rem -= mirLen;
    mir++;
  }
  utopianDate.mir = millennnia * 1000 + mir;

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
  utopianDate.month = month;

  // Calculate sol of the month.
  // Add 1 because if there is 0 whole sols left we are in the first sol of the month.
  utopianDate.sol = rem + 1;

  // Store the time.
  utopianDate.time = time;

  return utopianDate;
}

/**
 * Get the Utopian datetime as Date object.
 *
 * Currently only supports positive mirs.
 * @todo Update to add support for negative mirs.
 *
 * @param {object} utopianDate
 * @return {Date}
 */
function utopian2gregorian(utopianDate) {
  // Convert Utopian datetime to sols:
  var sols = 0;

  // Count the sols in all kilomirs before the current one.
  var kilomirs = Math.floor(utopianDate.mir / 1000);
  sols = kilomirs * SOLS_PER_KILOMIR;
  var mir = utopianDate.mir - 1000 * kilomirs;

  // Count the sols in all mirs before the current one.
  for (var m = 0; m < mir; m++) {
    sols += solsInMir(m);
  }

  // Count the sols in all months before the current one.
  for (var n = 0; n < utopianDate.month; n++) {
    sols += solsInMonth(utopianDate.mir, n);
  }

  // Count the remaining sols, including the fractional part, which is the time of day.
  sols += (utopianDate.sol - 1) + utopianDate.time;

  // Convert to milliseconds.
  var ms = (sols * MS_PER_SOL) + EPOCH_START_DATE;
  return new Date(ms);
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Constants and functions for calendar month and sol names.

// Names and abbreviated names of the Martian months.
var UTOPIAN_MONTH_NAMES = [
  undefined,
  ["Phe", "Phoenix",        "Phoenix",          "00 55.91"],
  ["Cet", "Cetus",          "Whale",            "01 40.10"],
  ["Dor", "Dorado",         "Dolphinfish",      "05 14.51"],
  ["Lep", "Lepus",          "Hare",             "05 33.95"],
  ["Col", "Columba",        "Dove",             "05 51.76"],
  ["Mon", "Monoceros",      "Unicorn",          "07 03.63"],
  ["Vol", "Volans",         "Flying Fish",      "07 47.73"],
  ["Lyn", "Lynx",           "Lynx",             "07 59.53"],
  ["Cam", "Camelopardalis", "Giraffe",          "08 51.37"],
  ["Cha", "Chamaeleon",     "Chameleon",        "10 41.53"],
  ["Hya", "Hydra",          "Sea Serpent",      "11 36.73"],
  ["Crv", "Corvus",         "Raven",            "12 26.52"],
  ["Cen", "Centaurus",      "Centaur",          "13 04.27"],
  ["Dra", "Draco",          "Dragon",           "15 08.64"],
  ["Lup", "Lupus",          "Wolf",             "15 13.21"],
  ["Aps", "Apus",           "Bird of Paradise", "16 08.65"],
  ["Pav", "Pavo",           "Peacock",          "19 36.71"],
  ["Aql", "Aquila",         "Eagle",            "19 40.02"],
  ["Vul", "Vulpecula",      "Fox",              "20 13.88"],
  ["Cyg", "Cygnus",         "Swan",             "20 35.28"],
  ["Del", "Delphinus",      "Dolphin",          "20 41.61"],
  ["Gru", "Grus",           "Crane",            "22 27.39"],
  ["Peg", "Pegasus",        "Pegasus",          "22 41.84"],
  ["Tuc", "Tucana",         "Toucan",           "23 46.64"]
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
function solName(nSolOfMonth, abbrev) {
  var name = UTOPIAN_SOL_NAMES[(nSolOfMonth - 1) % 7];
  return abbrev ? name.substr(0, 1) : name;
}
