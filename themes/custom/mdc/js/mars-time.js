/**
 * Created by shaun on 26/4/17.
 */

// To convert Earth time to Mars.
var MS_PER_SOL = 88775244.09;
var MS_PER_ZODE = MS_PER_SOL / 10;      // 8877524.409 ms
var MS_PER_MIL = MS_PER_ZODE / 100;     // 88775.24409 ms
var MS_PER_TAL = MS_PER_MIL / 100;      // 887.7524409 ms
var MS_PER_MICROSOL = MS_PER_TAL / 10;  // 88.77524409 ms
var SOLS_PER_KILOMIR = 668591;

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
  mir = Math.abs(mir);
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
 * Convert a Unix timestemp to a Utopian datetime.
 *
 * @todo Test!!
 *
 * @param {int} ts
 * @return object
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
  var solsRemaining = Math.floor(sols);
  var time = sols - solsRemaining;

  // Get the current millennia.
  var kilomirs = Math.floor(solsRemaining / SOLS_PER_KILOMIR);
  solsRemaining = solsRemaining % SOLS_PER_KILOMIR;

  // Calculate the mir.
  var mir = 0, mirLen;
  // There are probably optimisations possible here, by counting down centuries and decades.
  // Worst case the loop runs 999 times.
  while (true) {
    // If we have more sols left than there are in current mir, subtract the number of sols in the
    // current mir from the solsRemainingainder, and go to the next mir.
    mirLen = solsInMir(mir);
    // Done?
    if (solsRemaining <= mirLen) {
      break;
    }
    solsRemaining -= mirLen;
    mir++;
  }
  utopianDateTime.mir = (kilomirs * 1000) + mir;

  // Calculate the month.
  var month = 1, monthLen;
  // There are probably optimisations possible here by counting down quarters.
  // Worst case the loop runs 23 times.
  while (true) {
    // If we have more sols left than there are in current month, subtract the number of sols in the
    // current month from the solsRemainingainder, and go to the next month.
    monthLen = solsInMonth(mir, month);
    // Done?
    if (solsRemaining <= monthLen) {
      break;
    }
    solsRemaining -= monthLen;
    month++;
  }
  utopianDateTime.month = month;

  // Get the month name.
  utopianDateTime.monthName = utopianMonthName(month);

  // Calculate sol of the month.
  // Add 1 because if there is 0 whole sols left we are in the first sol of the month.
  utopianDateTime.sol = solsRemaining + 1;

  // Get the sol name.
  utopianDateTime.solName = utopianSolName(utopianDateTime.sol);

  // Store the time.
  utopianDateTime.time = time;

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
 * Convert a Utopian datetime object to a Unix timestamp.
 *
 * @todo Test!!
 *
 * @param {object} utopianDateTime
 * @return {int}
 */
function utopian2timestamp(utopianDateTime) {
  // Convert Utopian datetime to sols:
  var sols = 0;
  var m, mirs, kilomirs;

  // Count how many sols to the start of the mir.
  if (utopianDateTime.mir > 0) {
    // Positive mir.

    // Count the sols in all kilomirs before the current one.
    if (utopianDateTime.mir >= 1000) {
      kilomirs = Math.floor(utopianDateTime.mir / 1000);
      sols = kilomirs * SOLS_PER_KILOMIR;
      mirs = utopianDateTime.mir - (1000 * kilomirs);
    }
    else {
      mirs = utopianDateTime.mir;
    }

    // Count the sols in all mirs before the current one.
    for (m = 0; m < mirs; m++) {
      sols += solsInMir(m);
    }
  }
  else if (utopianDateTime.mir < 0) {
    // Negative mir.

    // Count the sols in all kilomirs before the current one.
    if (utopianDateTime.mir <= -1000) {
      kilomirs = Math.floor(-utopianDateTime.mir / 1000);
      sols = -kilomirs * SOLS_PER_KILOMIR;
      mirs = utopianDateTime.mir + (1000 * kilomirs);
    }
    else {
      mirs = utopianDateTime.mir;
    }

    // Count the sols in all mirs before the current one.
    for (m = -1; m >= mirs; m--) {
      sols -= solsInMir(m);
    }
  }

  // Count the sols in all months before the current one.
  for (var n = 1; n < utopianDateTime.month; n++) {
    sols += solsInMonth(utopianDateTime.mir, n);
  }

  // Count the solsRemainingaining sols, including the fractional part, which is the time of day.
  sols += (utopianDateTime.sol - 1) + utopianDateTime.time;

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
function utopianSolName(nSolOfMonth, abbrev) {
  var name = UTOPIAN_SOL_NAMES[(nSolOfMonth - 1) % 7];
  return abbrev ? name.substr(0, 1) : name;
}
