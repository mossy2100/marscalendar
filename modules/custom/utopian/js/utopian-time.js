/**
 * @file
 * Date and time functions for the Gregorian and Utopian calendars.
 *
 * Exposes Drupal.utopian, which the datetime converter and Mars clock use.
 * All functions are pure: nothing here touches the DOM.
 */

((Drupal) => {
  // ---------------------------------------------------------------------------
  // Constants.

  const MS_PER_DAY = 86400000;

  const SECONDS_PER_SOL = 88775.244147;
  const MS_PER_SOL = SECONDS_PER_SOL * 1000;
  const MS_PER_ZODE = MS_PER_SOL / 10;
  const MS_PER_MICROSOL = MS_PER_SOL / 1e6;

  const SOLS_PER_LONG_WEEK = 7;
  const SOLS_PER_SHORT_MONTH = 27;
  const SOLS_PER_LONG_MONTH = 28;
  const SOLS_PER_SHORT_QUARTER = 167;
  const SOLS_PER_SHORT_MIR = 668;
  const SOLS_PER_LONG_MIR = 669;
  const SOLS_PER_MIR = 668.591;
  const SOLS_PER_KILOMIR = SOLS_PER_MIR * 1000;

  const MONTHS_PER_QUARTER = 6;
  const MONTHS_PER_MIR = 24;

  /**
   * Start of the Utopian epoch, as a Unix timestamp in milliseconds.
   *
   * @see https://marscalendar.com/epoch
   */
  const EPOCH_START = Date.UTC(1609, 2, 12, 19, 19, 6);

  /**
   * Gregorian month names, indexed 1..12.
   */
  const GREGORIAN_MONTH_NAMES = [
    undefined,
    'January',
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

  /**
   * Gregorian day names, indexed 1..7 using ISO 8601 numbering (Monday = 1).
   */
  const GREGORIAN_DAY_NAMES = [
    undefined,
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  /**
   * Utopian month names, indexed 1..24.
   *
   * Each entry is [abbreviation, constellation name, English meaning, right ascension].
   */
  const UTOPIAN_MONTHS = [
    undefined,
    ['Phe', 'Phoenix', 'Phoenix', '00 55.91'],
    ['Cet', 'Cetus', 'Whale', '01 40.10'],
    ['Dor', 'Dorado', 'Dolphinfish', '05 14.51'],
    ['Lep', 'Lepus', 'Hare', '05 33.95'],
    ['Col', 'Columba', 'Dove', '05 51.76'],
    ['Mon', 'Monoceros', 'Unicorn', '07 03.63'],
    ['Vol', 'Volans', 'Flying Fish', '07 47.73'],
    ['Lyn', 'Lynx', 'Lynx', '07 59.53'],
    ['Cam', 'Camelopardalis', 'Giraffe', '08 51.37'],
    ['Cha', 'Chamaeleon', 'Chameleon', '10 41.53'],
    ['Hya', 'Hydra', 'Sea Serpent', '11 36.73'],
    ['Crv', 'Corvus', 'Raven', '12 26.52'],
    ['Cen', 'Centaurus', 'Centaur', '13 04.27'],
    ['Dra', 'Draco', 'Dragon', '15 08.64'],
    ['Lup', 'Lupus', 'Wolf', '15 13.21'],
    ['Aps', 'Apus', 'Bird of Paradise', '16 08.65'],
    ['Pav', 'Pavo', 'Peacock', '19 36.71'],
    ['Aql', 'Aquila', 'Eagle', '19 40.02'],
    ['Vul', 'Vulpecula', 'Fox', '20 13.88'],
    ['Cyg', 'Cygnus', 'Swan', '20 35.28'],
    ['Del', 'Delphinus', 'Dolphin', '20 41.61'],
    ['Gru', 'Grus', 'Crane', '22 27.39'],
    ['Peg', 'Pegasus', 'Pegasus', '22 41.84'],
    ['Tuc', 'Tucana', 'Toucan', '23 46.64'],
  ];

  /**
   * Utopian sol names, indexed 1..7.
   *
   * The order parallels the Terran days of the week (Monday..Sunday), with Earth taking Mars' place.
   */
  const UTOPIAN_SOL_NAMES = [
    undefined,
    'Lunasol',
    'Earthsol',
    'Mercurisol',
    'Jupitersol',
    'Venusol',
    'Saturnsol',
    'Sunsol',
  ];

  // ---------------------------------------------------------------------------
  // Number and string helpers.

  /**
   * Integer division, rounding towards negative infinity.
   *
   * @param {number} x
   * @param {number} y
   * @return {number}
   */
  const div = (x, y) => Math.floor(x / y);

  /**
   * Modulo that returns a result with the same sign as the divisor.
   *
   * @param {number} x
   * @param {number} y
   * @return {number}
   */
  const mod = (x, y) => x - div(x, y) * y;

  /**
   * Pad an integer with leading zeros to a minimum number of digits.
   *
   * @param {number} x
   * @param {number} digits
   * @return {string}
   */
  const padDigits = (x, digits) =>
    x < 0 ? `-${String(-x).padStart(digits, '0')}` : String(x).padStart(digits, '0');

  /**
   * Get the English ordinal suffix for an integer ('st', 'nd', 'rd' or 'th').
   *
   * @param {number} n
   * @return {string}
   */
  const ordinalSuffix = (n) => {
    const mod10 = mod(n, 10);
    const mod100 = mod(n, 100);
    if (mod10 === 1 && mod100 !== 11) {
      return 'st';
    }
    if (mod10 === 2 && mod100 !== 12) {
      return 'nd';
    }
    if (mod10 === 3 && mod100 !== 13) {
      return 'rd';
    }
    return 'th';
  };

  /**
   * Clamp an integer parsed from a string to a range, defaulting to the minimum.
   *
   * @param {string|undefined} value
   * @param {number} max
   * @return {number}
   */
  const parseClamped = (value, max) => {
    const n = parseInt(value, 10);
    if (Number.isNaN(n) || n < 0) {
      return 0;
    }
    return Math.min(n, max);
  };

  // ---------------------------------------------------------------------------
  // Gregorian calendar.

  /**
   * Check if a Gregorian year is a leap year.
   *
   * @param {number} year
   * @return {boolean}
   */
  const isLeapYear = (year) => {
    const y = Math.floor(year);
    return mod(y, 400) === 0 || (mod(y, 4) === 0 && mod(y, 100) !== 0);
  };

  /**
   * Get the number of days in a Gregorian month.
   *
   * @param {number} year
   * @param {number} month
   *   The month (1..12).
   * @return {number}
   */
  const daysInMonth = (year, month) => {
    if (month === 2) {
      return isLeapYear(year) ? 29 : 28;
    }
    return [4, 6, 9, 11].includes(month) ? 30 : 31;
  };

  /**
   * Get a Gregorian month name.
   *
   * @param {number} month
   *   The month (1..12).
   * @return {string}
   */
  const gregorianMonthName = (month) => GREGORIAN_MONTH_NAMES[month];

  /**
   * Get a Gregorian day name.
   *
   * @param {number} dayOfWeek
   *   The ISO 8601 day of the week (1..7).
   * @return {string}
   */
  const gregorianDayName = (dayOfWeek) => GREGORIAN_DAY_NAMES[dayOfWeek];

  /**
   * Get the Unix timestamp (ms) of midnight UTC on a date, supporting all years.
   *
   * Date.UTC() maps years 0..99 to 1900..1999, so set the full year explicitly.
   *
   * @param {number} year
   * @param {number} monthIndex
   *   The zero-based month (0..11).
   * @param {number} day
   * @return {number}
   */
  const utcMidnight = (year, monthIndex, day) => {
    const dt = new Date(0);
    dt.setUTCFullYear(year, monthIndex, day);
    return dt.valueOf();
  };

  /**
   * Get the day of the year (1..366) of a Date.
   *
   * @param {Date} date
   * @param {boolean} utc
   *   TRUE to use the UTC date, FALSE to use the local date.
   * @return {number}
   */
  const dayOfYear = (date, utc) => {
    const year = utc ? date.getUTCFullYear() : date.getFullYear();
    const monthIndex = utc ? date.getUTCMonth() : date.getMonth();
    const day = utc ? date.getUTCDate() : date.getDate();
    return (utcMidnight(year, monthIndex, day) - utcMidnight(year, 0, 1)) / MS_PER_DAY + 1;
  };

  /**
   * Get the ISO 8601 day of the week (1..7, Monday = 1) of a Date.
   *
   * @param {Date} date
   * @param {boolean} utc
   *   TRUE to use the UTC date, FALSE to use the local date.
   * @return {number}
   */
  const dayOfWeek = (date, utc) => (utc ? date.getUTCDay() : date.getDay()) || 7;

  /**
   * Format a time as HH:mm:ss.
   *
   * @param {number} hour
   * @param {number} minute
   * @param {number} second
   * @return {string}
   */
  const formatHms = (hour, minute, second) =>
    `${padDigits(hour, 2)}:${padDigits(minute, 2)}:${padDigits(second, 2)}`;

  /**
   * Normalise a user-entered Earth time string to HH:mm:ss.
   *
   * Missing or invalid parts become 0, and out-of-range parts are clamped.
   *
   * @param {string} time
   * @return {string}
   */
  const formatEarthTime = (time) => {
    const parts = String(time).split(':');
    return formatHms(
      parseClamped(parts[0], 23),
      parseClamped(parts[1], 59),
      parseClamped(parts[2], 59),
    );
  };

  // ---------------------------------------------------------------------------
  // Utopian calendar.

  /**
   * Check if a mir is a long mir (669 sols).
   *
   * Odd mirs are long. Even mirs are long if divisible by 10, except those divisible by 100 but not by 1000.
   *
   * @param {number} mir
   * @return {boolean}
   */
  const isLongMir = (mir) =>
    mir % 2 !== 0 || mir % 1000 === 0 || (mir % 100 !== 0 && mir % 10 === 0);

  /**
   * Get the number of sols in a mir.
   *
   * @param {number} mir
   * @return {number}
   */
  const solsInMir = (mir) => (isLongMir(mir) ? SOLS_PER_LONG_MIR : SOLS_PER_SHORT_MIR);

  /**
   * Get the number of sols in a month.
   *
   * @param {number} mir
   * @param {number} month
   *   The month (1..24).
   * @return {number}
   */
  const solsInMonth = (mir, month) => {
    if (month === MONTHS_PER_MIR) {
      return isLongMir(mir) ? SOLS_PER_LONG_MONTH : SOLS_PER_SHORT_MONTH;
    }
    return month % MONTHS_PER_QUARTER === 0 ? SOLS_PER_SHORT_MONTH : SOLS_PER_LONG_MONTH;
  };

  /**
   * Count the sols in mirs 1..mir. Mir 0 is not counted.
   *
   * @param {number} mir
   * @return {number}
   */
  const solsInMirs = (mir) =>
    mir * SOLS_PER_SHORT_MIR +
    Math.floor((mir + 1) / 2) +
    Math.floor(mir / 10) -
    Math.floor(mir / 100) +
    Math.floor(mir / 1000);

  /**
   * Get the sol of the mir (1..669).
   *
   * @param {number} month
   *   The month (1..24).
   * @param {number} solOfMonth
   *   The sol of the month (1..28).
   * @return {number}
   */
  const solOfMir = (month, solOfMonth) => {
    const q = Math.floor((month - 1) / MONTHS_PER_QUARTER);
    const m = month - q * MONTHS_PER_QUARTER - 1;
    return q * SOLS_PER_SHORT_QUARTER + m * SOLS_PER_LONG_MONTH + solOfMonth;
  };

  /**
   * Get a Utopian month name.
   *
   * @param {number} month
   *   The month (1..24).
   * @return {string}
   */
  const utopianMonthName = (month) => UTOPIAN_MONTHS[month][1];

  /**
   * Get a Utopian sol name.
   *
   * @param {number} solOfWeek
   *   The sol of the week (1..7).
   * @return {string}
   */
  const utopianSolName = (solOfWeek) => UTOPIAN_SOL_NAMES[solOfWeek];

  /**
   * Build a complete Utopian datetime object from its parts.
   *
   * @param {number} mir
   * @param {number} month
   * @param {number} solOfMonth
   * @param {number} mils
   * @param {number} timeZone
   * @return {object}
   *   The datetime, with mir, month, solOfMonth, mils and timeZone, plus the derived monthName, solOfWeek, solName and
   *   solOfMir.
   */
  const makeUtopian = (mir, month, solOfMonth, mils, timeZone) => {
    const solOfWeek = ((solOfMonth - 1) % SOLS_PER_LONG_WEEK) + 1;
    return {
      mir,
      month,
      solOfMonth,
      monthName: utopianMonthName(month),
      solOfWeek,
      solName: utopianSolName(solOfWeek),
      solOfMir: solOfMir(month, solOfMonth),
      mils,
      timeZone,
    };
  };

  /**
   * Convert a Unix timestamp to a Utopian datetime.
   *
   * @param {number} timestamp
   *   Unix timestamp in milliseconds.
   * @param {number} [timeZone=0]
   *   The Mars time zone (-5..5), in zodes from MTC.
   * @return {object}
   *   The datetime, as returned by makeUtopian().
   */
  const timestampToUtopian = (timestamp, timeZone = 0) => {
    // Sols since the epoch, rounded to microsols, adjusted for the time zone.
    let sols = (Math.round(timestamp) - EPOCH_START) / MS_PER_SOL;
    sols = Math.round(sols * 1e6) / 1e6 + timeZone / 10;

    const wholeSols = Math.floor(sols);
    let rem = wholeSols;

    // Kilomir -1 covers mirs -1000..-1, kilomir 0 covers 0..999, and so on.
    let kilomir = 0;
    if (rem > 0) {
      kilomir = Math.floor(rem / SOLS_PER_KILOMIR);
    }
    else if (rem < 0) {
      kilomir = Math.ceil(rem / SOLS_PER_KILOMIR) - 1;
    }
    rem -= kilomir * SOLS_PER_KILOMIR;

    // Mir.
    const mirs = Math.floor(rem / SOLS_PER_MIR);
    let mir = kilomir * 1000 + mirs;
    if (mirs > 0) {
      rem -= SOLS_PER_LONG_MIR + solsInMirs(mirs - 1);
    }
    const mirLength = solsInMir(mir);
    if (rem >= mirLength) {
      rem -= mirLength;
      mir++;
    }

    // Quarter (0..3).
    let month = 1;
    const q = Math.min(Math.floor(rem / SOLS_PER_SHORT_QUARTER), 3);
    if (q > 0) {
      month += q * MONTHS_PER_QUARTER;
      rem -= q * SOLS_PER_SHORT_QUARTER;
    }

    // Month within the quarter.
    const m = Math.floor(rem / SOLS_PER_LONG_MONTH);
    if (m > 0) {
      month += m;
      rem -= m * SOLS_PER_LONG_MONTH;
    }

    // Sol of the month (1..28), and the time of sol in mils.
    const solOfMonth = rem + 1;
    const mils = Math.round((sols - wholeSols) * 1e6) / 1e3;

    return makeUtopian(mir, month, solOfMonth, mils, timeZone);
  };

  /**
   * Convert a Utopian datetime to a Unix timestamp.
   *
   * @param {object} dtMars
   *   An object with mir, month, solOfMonth, mils and (optionally) timeZone.
   * @return {number}
   *   Unix timestamp in milliseconds.
   */
  const utopianToTimestamp = (dtMars) => {
    // Sols from the start of the epoch to the start of the mir.
    let sols = 0;
    if (dtMars.mir > 0) {
      sols = SOLS_PER_LONG_MIR + solsInMirs(dtMars.mir - 1);
    }
    else if (dtMars.mir < 0) {
      sols = -solsInMirs(-dtMars.mir);
    }

    // Sols in the months before this one.
    const n = dtMars.month - 1;
    const q = Math.floor(n / MONTHS_PER_QUARTER);
    sols += q * SOLS_PER_SHORT_QUARTER + (n - q * MONTHS_PER_QUARTER) * SOLS_PER_LONG_MONTH;

    // Sols in this month before this one, then the time of sol.
    sols += dtMars.solOfMonth - 1;
    sols += dtMars.mils / 1e3;

    // Time zone offset.
    if (dtMars.timeZone !== undefined) {
      sols -= dtMars.timeZone / 10;
    }

    return Math.round(EPOCH_START + sols * MS_PER_SOL);
  };

  /**
   * Format a Mars time of sol, given in mils, as 999.999.
   *
   * Invalid or negative input gives 000.000, and input past the end of the sol gives 999.999.
   *
   * @param {number|string} mils
   * @return {string}
   */
  const formatMarsTime = (mils) => {
    const value = typeof mils === 'string' ? parseFloat(mils) : mils;
    if (typeof value !== 'number' || Number.isNaN(value) || value < 0) {
      return '000.000';
    }
    const microsols = Math.min(Math.round(value * 1000), 999999);
    return `${padDigits(div(microsols, 1000), 3)}.${padDigits(mod(microsols, 1000), 3)}`;
  };

  /**
   * Format a Utopian datetime as M{mir}/{month}/{sol}T{time}, e.g. M215/03/14T512.345.
   *
   * @param {object} dtMars
   * @return {string}
   */
  const formatUtopian = (dtMars) =>
    `M${dtMars.mir}/${padDigits(dtMars.month, 2)}/${padDigits(dtMars.solOfMonth, 2)}T${formatMarsTime(dtMars.mils)}`;

  Drupal.utopian = Object.freeze({
    MS_PER_ZODE,
    MS_PER_MICROSOL,
    SOLS_PER_LONG_MONTH,
    MONTHS_PER_MIR,
    padDigits,
    ordinalSuffix,
    isLeapYear,
    daysInMonth,
    gregorianMonthName,
    gregorianDayName,
    dayOfYear,
    dayOfWeek,
    formatHms,
    formatEarthTime,
    isLongMir,
    solsInMir,
    solsInMonth,
    solOfMir,
    utopianMonthName,
    utopianSolName,
    makeUtopian,
    timestampToUtopian,
    utopianToTimestamp,
    formatMarsTime,
    formatUtopian,
  });
})(Drupal);
