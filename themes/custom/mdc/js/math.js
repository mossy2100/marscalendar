/**
 * Created 2017-08-05.
 * @author Shaun Moss (shaun@astromultimedia.com)
 *
 * Bonus Math functions.
 */

/**
 * Integer division.
 *
 * @param {int} x
 * @param {int} y
 * @return {int}
 */
Math.div = function (x, y) {
  // Coerce operands to integers.
  x = parseInt(x, 10);
  if (isNaN(x)) {
    return undefined;
  }
  y = parseInt(y, 10);
  if (isNaN(y)) {
    return undefined;
  }

  // Integer division.
  return Math.floor(x / y);
};

/**
 * Modulo function that correctly supports negative numbers.
 *
 * @param {int} x
 * @param {int} y
 * @return {int}
 */
Math.mod = function (x, y) {
  // Coerce operands to integers.
  x = parseInt(x, 10);
  if (isNaN(x)) {
    return undefined;
  }
  y = parseInt(y, 10);
  if (isNaN(y)) {
    return undefined;
  }

  // Non-negative operands work fine with built-in modulo operator.
  if (x >= 0 && y >= 0) {
    return x % y;
  }

  // Handle negative operands correctly.
  return x - (Math.div(x, y) * y);
};

/**
 * Calculate the ordinal suffix for a number.
 *
 * @param {int} n
 * @return {string}
 */
function ordinalSuffix(n) {
  var mod10 = Math.mod(n, 10);
  var mod100 = Math.mod(n, 100);
  if (mod10 == 1 && mod100 != 11) {
    return 'st';
  }
  else if (mod10 == 2 && mod100 != 12) {
    return 'nd';
  }
  else if (mod10 == 3 && mod100 != 13) {
    return 'rd';
  }
  return 'th';
}

/**
 * Append to a number its ordinal suffix as a superscript.
 *
 * @param {int} n
 * @return {string}
 */
function appendOrdinalSuffix(n) {
  return n + '<sup>' + ordinalSuffix(n) + '</sup>';
}
