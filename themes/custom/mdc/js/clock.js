/**
 * For clock.
 */

(function($) {

  /**
   * Add characters to start of a string until desired length reached.
   */
  function padLeft(value, width, chPad) {
    // Convert to a string.
    var result = value + "";
    // Add pad characters until desired width is reached.
    while (result.length < width) {
      result = chPad + result;
    }
    return result;
  }

  /**
   * Pad a number to a certain number of digits.
   */
  function padDigits(x, digits) {
    return (x < 0) ? ("-" + padLeft(-x, digits, "0")) : padLeft(x, digits, "0");
  }

  /**
   * Update the clocks every microsol.
   */
  function showTime() {
    // Get current Earth time:
    var earthNow = new Date();

    // Get corresponding Mars Date:
    var marsNow = gregorian2utopian(earthNow);

    // Set clock fields.
    var marsMonth = utopianMonthName(marsNow.month);

    var marsDateStr = 'M' + marsNow.mir + ' ' + marsMonth + ' ' + marsNow.sol;
    $('#marsClockDate').html(marsDateStr);

    var solStr = solName(marsNow.sol);
    $('#marsClockSol').html(solStr);

    var mils = marsNow.time * 1000;
    var wholeMils = Math.floor(mils);
    var microsols = Math.floor((mils - wholeMils) * 1000);
    var marsTimeStr = padDigits(wholeMils, 3) + '.' + padDigits(microsols, 3);
    $('#marsClockTime').html(marsTimeStr);

    // When next to call showtime:
    setTimeout(showTime, 89);
  }

  $(showTime);

})(jQuery);
