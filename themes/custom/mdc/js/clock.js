/**
 * For clock.
 */

(function($) {

  var timerID2 = null;
  var timerRunning2 = false;

  /**
   * Update the clocks every microsol.
   */
  function showTime() {
    // Get current Earth time:
    var EarthNow = new Date();

    // Get corresponding Mars Date:
    var MarsNow = DateToMarsDate(EarthNow);

    // Set clock fields.
    var oldMarsTime2 = document.Clock.MarsTime2.value;
    var newMarsTime2;

    newMarsTime2 = FormatMarsTime2Stretched(MarsNow2);

    document.Clock.MarsTime2.value = newMarsTime2;

    if (newMarsTime2 != oldMarsTime2) {
      document.Clock.MarsSol2.value = SolName(MarsNow2.Sol);
      document.Clock.MarsDate2.value = FormatMarsDate2(MarsNow2);
    }

    // Earth:
    var oldEarthTime2 = document.Clock.EarthTime2.value;
    var newEarthTime2;
    newEarthTime2 = FormatTime(EarthNow2);
    document.Clock.EarthTime2.value = newEarthTime2;
    if (newEarthTime2 != oldEarthTime2) {
      document.Clock.EarthDay2.value = EnglishDayName(EarthNow2.getUTCDay());
      document.Clock.EarthDate2.value = FormatDate(EarthNow2);
    }

    // when next to call showtime:
    timerID2 = setTimeout("showTime()", 89);
    timerRunning2 = true;
  }

  $(showTime);

})(jQuery);
