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
