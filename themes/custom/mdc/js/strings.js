/**
 * Created by PhpStorm at 2017-08-05T17:29
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 * Misc string functions.
 */

/**
 * Add characters to start of a string until desired minimum width is reached.
 *
 * @param {string} value
 * @param {int} minWidth
 * @param {string} chPad
 * @returns {string}
 */
function padLeft(value, minWidth, chPad) {
  // Convert to a string.
  var result = value + '';
  // If necessary, add pad characters until desired width is reached.
  var n = minWidth - result.length;
  if (n > 0) {
    result = chPad.repeat(n) + result;
  }
  return result;
}

/**
 * Pad a number to a certain number of digits.
 *
 * @param {int} x
 * @param {int} digits
 * @returns {string}
 */
function padDigits(x, digits) {
  return (x < 0) ? ('-' + padLeft(-x, digits, '0')) : padLeft(x, digits, '0');
}
