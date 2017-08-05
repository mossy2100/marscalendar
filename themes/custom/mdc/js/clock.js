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
    var tzOffset = parseInt($('#mars-clock-tz').val(), 10);
    ts += tzOffset * MS_PER_ZODE;

    // Get Utopian datetime.
    var marsNow = timestamp2utopian(ts);

    // Date.
    var marsDateStr = 'M' + marsNow.mir + '/' + padDigits(marsNow.month, 2) + '/' +
      padDigits(marsNow.solOfMonth, 2);
    $('#mars-clock-date').html(marsDateStr);

    // Month name.
    $('#mars-clock-month-name').html(marsNow.monthName);

    // Time.
    var marsTimeStr = formatMarsTime(marsNow.mils);
    $('#mars-clock-time').html(marsTimeStr);

    // Sol name.
    $('#mars-clock-sol-name').html(marsNow.solName);

    // Sol number of the week.
    $('#mars-clock-sol-of-week').html(appendOrdinalSuffix(marsNow.solOfWeek));

    // Sol number of the mir.
    $('#mars-clock-sol-of-mir').html(appendOrdinalSuffix(marsNow.solOfMir));

    // The mir.
    $('#mars-clock-mir').html('M' + marsNow.mir);

    // Call this function again in 1 µsol.
    setTimeout(showTime, MS_PER_MICROSOL);
  }

  $(showTime);

})(jQuery);
