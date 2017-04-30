/**
 * Created by shaun on 28/4/17.
 *
 * Loop to run the Mars clock.
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
  var result = value + "";
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
  return (x < 0) ? ("-" + padLeft(-x, digits, "0")) : padLeft(x, digits, "0");
}

(function($) {

  /**
   * Update the clocks every microsol.
   */
  function showTime() {
    // Get current Unix timestamp.
    var ts = Date.now();

    // Adjust for timezone.
    var tzOffset = parseInt($('#marsClockTimeZone select').val(), 10);
    ts += tzOffset * MS_PER_ZODE;

    // Get Utopian datetime.
    var marsNow = timestamp2utopian(ts);

    // Sol name.
    $('#marsClockSol').html(marsNow.solName);

    var marsDateStr = 'M' + marsNow.mir + ' ' + marsNow.monthName + ' ' + marsNow.sol;
    $('#marsClockDate').html(marsDateStr);

    var marsTimeStr = 'm' + formatMarsTime(marsNow.mils, 3);
    $('#marsClockTime').html(marsTimeStr);

    // Call this function again in 1 µsol.
    setTimeout(showTime, MS_PER_MICROSOL);
  }

  $(showTime);

})(jQuery);
