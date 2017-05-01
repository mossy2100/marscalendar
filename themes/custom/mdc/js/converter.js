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
    var dtMars = gregorian2utopian(dtEarth);
    setMarsDatetime(dtMars);

    // Assign button actions.
    $('#btn-convert-earth2mars').click(function () {
      event.preventDefault();
      var dtEarth = getEarthDatetime();
      var dtMars = gregorian2utopian(dtEarth);
      setMarsDatetime(dtMars);
    });

    $('#btn-convert-mars2earth').click(function () {
      event.preventDefault();
      var dtMars = getMarsDatetime();
      var dtEarth = utopian2gregorian(dtMars);
      setEarthDatetime(dtEarth);
    });

    // Assign selector behaviours.
    $("#earth-datetime").change(function() {
      setDayOfWeekAndYear();
    });

    $(".mars-date").change(function() {
      updateSolSelector();
      setSolOfWeekAndMir();
    });

    $('#mils').change(function() {
      // Reformat the mils like 999.999.
      var mils = parseFloat($("#mils").val(), 10);
      if (mils < 0) {
        mils = 0;
      }
      if (mils > 999.999) {
        mils = 999.999;
      }
      $("#mils").val(formatMarsTime(dtMars.mils, 3));
    });
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
    var hour = dtEarth.getHours();
    var minute = dtEarth.getMinutes();
    var dateTimeStr = padDigits(year, 4) + '-' + padDigits(month, 2) + '-' + padDigits(day, 2) + 'T' + padDigits(hour, 2) + ':' + padDigits(minute, 2);
    $("#earth-datetime").val(dateTimeStr);

    // Set the day of the week and the year.
    setDayOfWeekAndYear(dtEarth);
  }

  /**
   * Get the Mars datetime from the form.
   *
   * @returns {object}
   */
  function getMarsDatetime() {
    var mir = parseInt($("#mir").val(), 10);
    var month = parseInt($("#mars-month").val(), 10);
    var sol = parseInt($("#sol").val(), 10);
    var mils = parseFloat($("#mils").val(), 10);
    var dtMars = {mir: mir, month: month, sol: sol, mils: mils};
    return dtMars;
  }

  /**
   * Set the Mars datetime on the form.
   *
   * @param {object} dtMars
   */
  function setMarsDatetime(dtMars) {
    // Set the mir.
    $("#mir").val(dtMars.mir);

    // Set the month.
    $("#mars-month").val(dtMars.month);

    // Update the sol selector.
    // updateSolSelector();

    // Set the sol.
    $("#sol").val(dtMars.sol);

    // Set the time.
    var marsTimeStr = formatMarsTime(dtMars.mils, 3);
    $("#mils").val(marsTimeStr);

    // Set the sol of the week and the mir.
    setSolOfWeekAndMir(dtMars);
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
  function setDayOfWeekAndYear(dtEarth) {
    if (dtEarth === undefined) {
      dtEarth = getEarthDatetime();
    }

    // Set the day of the week.
    var dayOfWeek = dtEarth.getDay();
    $("#day-of-week").html(gregorianDayName(dayOfWeek));

    // Set the day of the year.
    var dtYearStart = new Date(dtEarth.getFullYear(), 0, 1);
    var dayOfYear = Math.floor((dtEarth - dtYearStart) / MS_PER_DAY) + 1;
    $("#day-of-year").html(dayOfYear);
  }

  /**
   * Update the sol of the week and mir to match the selected Mars date.
   */
  function setSolOfWeekAndMir(dtMars) {
    if (dtMars === undefined) {
      dtMars = getMarsDatetime();
    }

    // Set the sol of the week.
    $("#sol-of-week").html(utopianSolName(dtMars.sol));

    // Set the sol of the mir.
    $("#sol-of-mir").html(solOfMir(dtMars.month, dtMars.sol));
  }

  $(initConverter);

})(jQuery);
