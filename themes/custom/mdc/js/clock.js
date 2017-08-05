/**
 * Created by shaun on 2017-04-28.
 * Loop to run the Mars clock.
 */

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

    var marsTimeStr = formatMarsTime(marsNow.mils);
    $('#marsClockTime').html(marsTimeStr);

    // Call this function again in 1 µsol.
    setTimeout(showTime, MS_PER_MICROSOL);
  }

  $(showTime);

})(jQuery);
