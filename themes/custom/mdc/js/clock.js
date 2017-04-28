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
    // Get current Unix timestamp.
    var ts = (new Date()).valueOf();

    // Adjust for timezone.
    ts += $('#marsClockTimeZone select').val() * MS_PER_ZODE;

    // Get Utopian datetime.
    var marsNow = timestamp2utopian(ts);

    // Sol name.
    $('#marsClockSol').html(marsNow.solName);

    var marsDateStr = 'M' + marsNow.mir + ' ' + marsNow.monthName + ' ' + marsNow.sol;
    $('#marsClockDate').html(marsDateStr);

    var mils = marsNow.time * 1000;
    var wholeMils = Math.floor(mils);
    var microsols = Math.floor((mils - wholeMils) * 1000);
    var marsTimeStr = padDigits(wholeMils, 3) + '.' + padDigits(microsols, 3);
    $('#marsClockTime').html(marsTimeStr);

    // Call this function again in one microsol.
    setTimeout(showTime, MS_PER_MICROSOL);
  }

  $(showTime);

})(jQuery);
