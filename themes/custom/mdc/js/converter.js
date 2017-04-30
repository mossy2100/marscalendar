/**
 * Created by shaun on 29/4/2017.
 *
 * For the Earth to Mars datetime converter.
 */

(function ($) {

  /**
   * Initialise the datetime converter.
   */
  function initConverter() {
    // Initialise the Mars date selectors.
    initSolSelector();
    initMarsMonthSelector();

    // Get the current datetime.
    var dtEarth = new Date();
    dtEarth.setSeconds(0);
    dtEarth.setMilliseconds(0);

    // Set the Earth datetime fields.
    setEarthDatetime(dtEarth);

    // Set the Mars datetime fields.
    setMarsDatetime(dtEarth);

    // Assign button actions.
    $('#btn-convert-earth2mars').click(function () {
      event.preventDefault();
      var dtEarth = getEarthDatetime();
      setMarsDatetime(dtEarth);
    });

    $('#btn-convert-mars2earth').click(function () {
      event.preventDefault();
      var dtEarth = getMarsDatetime();
      if (dtEarth !== false) {
        setEarthDatetime(dtEarth);
      }
    });

    // Assign selector behaviours.
    $("#earth-datetime").change(updateDayOfWeek);
    $("#mir").change(updateSolSelector);
    $("#mars-month").change(updateSolSelector);
  }

  /**
   * Get the Earth datetime from the form.
   *
   * @returns {Date}
   */
  function getEarthDatetime() {
    var dtStr = $("#earth-datetime").val();
    // Format is like 2017-04-27T15:00
    var year = parseInt(dtStr.substr(0, 4), 10);
    var month = parseInt(dtStr.substr(5, 2), 10);
    var day = parseInt(dtStr.substr(8, 2), 10);
    var hour = parseInt(dtStr.substr(11, 2), 10);
    var minute = parseInt(dtStr.substr(14, 2), 10);
    var dtEarth = new Date(year, month - 1, day, hour, minute);
    return dtEarth;
  }

  /**
   * Set the Earth datetime on the form.
   *
   * @param {Date} dtEarth
   */
  function setEarthDatetime(dtEarth) {
    var year = dtEarth.getFullYear();
    var month = dtEarth.getMonth() + 1;
    var day = dtEarth.getDate();
    var dayOfWeek = dtEarth.getDay();
    var hour = dtEarth.getHours();
    var minute = dtEarth.getMinutes();
    var dateTimeStr = padDigits(year, 4) + '-' + padDigits(month, 2) + '-' + padDigits(day, 2) + 'T' + padDigits(hour, 2) + ':' + padDigits(minute, 2);
    $("#earth-datetime").val(dateTimeStr);
    $("#day-of-week").html(gregorianDayName(dayOfWeek));
  }

  /**
   * Get the Mars datetime from the form.
   *
   * @returns {Date|boolean}
   */
  function getMarsDatetime() {
    var mir = parseInt($("#mir").val(), 10);
    var month = parseInt($("#mars-month").val(), 10);
    var sol = parseInt($("#sol").val(), 10);
    if (sol < 1 || sol > 28) {
      // Show an error.
      return false;
    }
    var mils = parseFloat($("#mils").val(), 10);
    if (mils < 0 || mils > 999.999) {
      // Show an error.
      return false;
    }
    var dtMars = {mir: mir, month: month, sol: sol, mils: mils};
    var dtEarth = utopian2gregorian(dtMars);
    dtEarth.setSeconds(0);
    dtEarth.setMilliseconds(0);
    return dtEarth;
  }

  /**
   * Set the Mars datetime on the form.
   *
   * @param {Date} dtEarth
   */
  function setMarsDatetime(dtEarth) {
    var dtMars = gregorian2utopian(dtEarth);
    $("#mir").val(dtMars.mir);
    $("#mars-month").val(dtMars.month);
    updateSolSelector();
    $("#sol").val(dtMars.sol);
    var marsTimeStr = formatMarsTime(dtMars.mils, 3);
    $("#mils").val(marsTimeStr);
    $("#sol-of-week").html(dtMars.solName);
  }

  /**
   * Initialise the sol selector.
   */
  function initSolSelector() {
    var solSelector = $('#sol');
    for (var i = 1; i <= 28; i++) {
      solSelector.append($('<option>', {id: 'sol' + i, value: i, text: i}));
    }
  }

  /**
   * Initialise the Mars month selector.
   */
  function initMarsMonthSelector() {
    var monthSelector = $('#mars-month');
    for (var i = 1; i <= 24; i++) {
      monthSelector.append($('<option>', {value: i, text: i + ' - ' + UTOPIAN_MONTH_NAMES[i][1]}));
    }
  }

  /**
   * Set the options in the sol selector.
   */
  function updateSolSelector() {
    var mir = parseInt($("#mir").val(), 10);
    var month = parseInt($("#mars-month").val(), 10);
    var n = solsInMonth(mir, month);
    var display = (n == 28) ? 'block' : 'none';
    $('#sol28').css('display', display);
    if (n == 27 && $('#sol').val() == 28) {
      $('#sol').val(27);
    }
  }

  /**
   * Update the day of the week to match the selected Earth date.
   */
  function updateDayOfWeek() {
    var dtEarth = getEarthDatetime();
    var dayOfWeek = dtEarth.getDay();
    $("#day-of-week").html(gregorianDayName(dayOfWeek));
  }

  $(initConverter);

})(jQuery);
