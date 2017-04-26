///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// for clock:
var timerID2 = null;
var timerRunning2 = false;
var EarthNow2 = null;
var MarsNow2 = null;
i = 1;

// not using these two functions but might as well keep them JIC:
function stopClock() {
  if (timerRunning2) {
    clearTimeout(timerID2);
  }
  timerRunning2 = false;
}

function startClock() {
  stopClock();
  showTime();
}

// updates the clocks every microsol:
function showTime() {
  // get current Earth time:
  var EarthNow2 = new Date();

  // get corresponding Mars Date:
  var MarsNow2 = new MarsDate2(EarthNow2);

  // set clock fields:
  // Mars
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
