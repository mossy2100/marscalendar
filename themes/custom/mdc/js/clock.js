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

    // Datetime.
    var marsDatetimeStr = 'M' + marsNow.mir + '/' + padDigits(marsNow.month, 2) + '/' +
      padDigits(marsNow.solOfMonth, 2) + ':' + formatMarsTime(marsNow.mils);
    $('#mars-clock-datetime').html(marsDatetimeStr);

    // Month name.
    $('#mars-clock-month-name').html(marsNow.monthName);

    // Sol name.
    $('#mars-clock-sol-name').html(marsNow.solName);

    // Sol number of the week.
    $('#mars-clock-sol-of-week').html(appendOrdinalSuffix(marsNow.solOfWeek));

    // Sol number of the mir.
    $('#mars-clock-sol-of-mir').html(appendOrdinalSuffix(marsNow.solOfMir));

    // Call this function again in 1 microsol.
    setTimeout(showTime, MS_PER_MICROSOL);
  }

  $(function() {
    // Check the clock is visible.
    var $block = $("#block-utopiandateandtime");
    if (!$block.length) {
      return;
    }

    // Load the clock HTML.
    $block.load("/sites/default/files/html/clock.html");

    // Display the time, updating every microsol.
    showTime();
  });

})(jQuery);
