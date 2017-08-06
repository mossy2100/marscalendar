/**
 * Created by shaun on 29/4/2017.
 *
 * For the Earth to Mars datetime converter.
 */

var currentYear, dtEarth, currentMir, dtMars;

(function ($) {

  /**
   * Initialise the datetime converter.
   */
  function initConverter() {
    // Initialise the selectors.
    initDaySelector();
    initEarthMonthSelector();
    initSolSelector();
    initMarsMonthSelector();

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Reset actions.

    // Initialise datetimes to now.
    resetDatetimes();

    // Reset the datetimes when the reset link is clicked.
    $('#btn-reset-converter').click(resetDatetimes);

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Actions for when Earth fields change.

    // When the year changes, reformat.
    var $year = $('#year');
    $year.change(function() {
      var year = parseInt($year.val(), 10);
      if (isNaN(year)) {
        // Default to current year.
        year = currentYear;
      }
      $year.val(year);
      dtEarth = getEarthDatetime();
    });

    // When the Earth time changes, reformat.
    var $earthTime = $('#earth-time');
    $earthTime.change(function() {
      $earthTime.val(formatEarthTime($earthTime.val()));
      dtEarth = getEarthDatetime();
    });

    // Events when any Earth datetime field changes.
    $(".earth-date").change(function() {
      updateDaySelector();
      dtEarth = getEarthDatetime();
      setEarthDerivedValues(dtEarth);
      // Update the Mars datetime.
      dtMars = gregorian2utopian(dtEarth, dtMars.timeZone);
      setMarsDatetime(dtMars);
    });

    ////////////////////////////////////////////////////////////////////////////////////////////////
    // Actions for when Mars fields change.

    // When the mir changes, reformat.
    var $mir = $('#mir');
    $mir.change(function() {
      var mir = parseInt($mir.val(), 10);
      if (isNaN(mir)) {
        // Default to current mir.
        mir = currentMir;
      }
      $mir.val(mir);
      dtMars = getMarsDatetime();
    });

    // When the Mars time changes, reformat.
    var $marsTime = $('#mars-time');
    $marsTime.change(function() {
      $marsTime.val(formatMarsTime($marsTime.val()));
      dtMars = getMarsDatetime();
    });

    // Events when any Mars datetime field changes.
    $(".mars-date").change(function() {
      updateSolSelector();
      dtMars = getMarsDatetime();
      setMarsDerivedValues(dtMars);
      // Update the Earth datetime.
      dtEarth = utopian2gregorian(dtMars);
      setEarthDatetime(dtEarth);
    });
  }

  /**
   * Set the Earth and Mars datetimes to now.
   */
  function resetDatetimes() {
    // Get the current Earth datetime.
    dtEarth = new Date();
    dtEarth.setMilliseconds(0);
    currentYear = dtEarth.getFullYear();

    // Set the Earth datetime fields.
    setEarthDatetime(dtEarth);

    // Get the current Mars datetime.
    var timeZone = dtMars && dtMars.timeZone ? dtMars.timeZone : 0;
    dtMars = gregorian2utopian(dtEarth, timeZone);
    currentMir = dtMars.mir;

    // Set the Mars datetime fields.
    setMarsDatetime(dtMars);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Earth datetime functions.

  /**
   * Get the Earth datetime from the form.
   *
   * @returns {object}
   */
  function getEarthDatetime() {
    var year = parseInt($("#year").val(), 10);
    var month = parseInt($("#earth-month").val(), 10);
    var day = parseInt($("#day").val(), 10);
    var time = $("#earth-time").val();
    var parts = time.split(':');
    var hour = parseInt(parts[0], 10);
    var minute = parseInt(parts[1], 10);
    var second = parseInt(parts[2], 10);
    var timeZone = $('#earth-time-zone').val();

    // Construct the Date object, using different methods depending on timezone setting.
    var timestamp, dt;
    if (timeZone == 'local') {
      dt = new Date(2000, month - 1, day, hour, minute, second, 0);
      dt.setFullYear(year);
    }
    else {
      timestamp = Date.UTC(2000, month - 1, day, hour, minute, second, 0);
      dt = new Date(timestamp);
      dt.setUTCFullYear(year);
    }

    return dt;
  }

  /**
   * Set the Earth datetime on the form.
   *
   * @param {Date} dtEarth
   */
  function setEarthDatetime(dtEarth) {
    var year, month, day, hour, minute, second;
    var timeZone = $('#earth-time-zone').val();

    if (timeZone == 'local') {
      year = dtEarth.getFullYear();
      month = dtEarth.getMonth() + 1;
      day = dtEarth.getDate();
      hour = dtEarth.getHours();
      minute = dtEarth.getMinutes();
      second = dtEarth.getSeconds();
    }
    else {
      year = dtEarth.getUTCFullYear();
      month = dtEarth.getUTCMonth() + 1;
      day = dtEarth.getUTCDate();
      hour = dtEarth.getUTCHours();
      minute = dtEarth.getUTCMinutes();
      second = dtEarth.getUTCSeconds();
    }

    // Update the year field.
    $("#year").val(year);

    // Update the month field.
    $("#earth-month").val(month);

    // Update the day field.
    $("#day").val(day);

    // Update the time field.
    $("#earth-time").val(formatHms(hour, minute, second));

    // Set the day of the week and the year.
    setEarthDerivedValues(dtEarth);
  }

  /**
   * Initialise the day selector.
   */
  function initDaySelector() {
    var daySelector = $('#day');
    var label;
    for (var i = 1; i <= 31; i++) {
      label = (i < 10 ? '0' : '') + i;
      daySelector.append($('<option>', {id: 'day' + i, value: i, text: label}));
    }
  }

  /**
   * Initialise the Earth month selector.
   */
  function initEarthMonthSelector() {
    var monthSelector = $('#earth-month');
    var label;
    for (var i = 1; i <= 12; i++) {
      label = (i < 10 ? '0' : '') + i;
      monthSelector.append($('<option>', {value: i, text: label}));
    }
  }

  /**
   * Set the options in the day selector.
   */
  function updateDaySelector() {
    var year = parseInt($("#year").val(), 10);
    var month = parseInt($("#earth-month").val(), 10);
    var n = daysInMonth(year, month);
    for (var d = 29; d <= 31; d++) {
      $('#day' + d).css('display', (d <= n) ? 'block' : 'none');
    }
    var $day = $('#day');
    if ($day.val() > n) {
      $day.val(n);
    }
  }

  /**
   * Update the day of the week to match the selected Earth date.
   *
   * @param {Date} dtEarth
   */
  function setEarthDerivedValues(dtEarth) {
    var month, dayOfWeek, dayOfYear;

    var utc = $('#earth-time-zone').val() == 'utc';
    if (utc) {
      month = dtEarth.getUTCMonth() + 1;
      dayOfWeek = dtEarth.getUTCDayOfWeek();
      dayOfYear = dtEarth.getUTCDayOfYear();
    }
    else {
      month = dtEarth.getMonth() + 1;
      dayOfWeek = dtEarth.getDayOfWeek();
      dayOfYear = dtEarth.getDayOfYear();
    }

    // Set the month name.
    $("#earth-month-name").html(gregorianMonthName(month));

    // Set the day of the week name.
    $("#day-name").html(gregorianDayName(dayOfWeek));

    // Set the day of the week number (uses ISO 8601 numbering).
    $("#day-of-week").html(appendOrdinalSuffix(dayOfWeek));

    // Set the day of the year number.
    $("#day-of-year").html(appendOrdinalSuffix(dayOfYear));
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Mars datetime functions.

  /**
   * Get the Mars datetime from the form.
   *
   * @returns {object}
   */
  function getMarsDatetime() {
    var mir = parseInt($("#mir").val(), 10);
    var month = parseInt($("#mars-month").val(), 10);
    var solOfMonth = parseInt($("#sol").val(), 10);
    var mils = parseFloat($("#mars-time").val(), 10);
    var solOfWeek = (solOfMonth - 1) % SOLS_PER_LONG_WEEK + 1;
    var timeZone = parseInt($('#mars-time-zone').val(), 10);

    // Generate the datetime object.
    return {
      mir: mir,
      month: month,
      solOfMonth: solOfMonth,
      monthName: utopianMonthName(month),
      solOfWeek: solOfWeek,
      solName: utopianSolName(solOfWeek),
      solOfMir: solOfMir(month, solOfMonth),
      mils: mils,
      timeZone: timeZone
    };
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

    // Set the sol.
    $("#sol").val(dtMars.solOfMonth);

    // Set the time.
    $("#mars-time").val(formatMarsTime(dtMars.mils));

    // Set the time zone.
    $("#mars-time-zone").val(dtMars.timeZone);

    // Set the sol of the week and the mir.
    setMarsDerivedValues(dtMars);
  }

  /**
   * Initialise the sol selector.
   */
  function initSolSelector() {
    var solSelector = $('#sol');
    var label;
    for (var i = 1; i <= 28; i++) {
      label = (i < 10 ? '0' : '') + i;
      solSelector.append($('<option>', {id: 'sol' + i, value: i, text: label}));
    }
  }

  /**
   * Initialise the Mars month selector.
   */
  function initMarsMonthSelector() {
    var monthSelector = $('#mars-month');
    var label;
    for (var i = 1; i <= 24; i++) {
      label = (i < 10 ? '0' : '') + i;
      monthSelector.append($('<option>', {value: i, text: label}));
    }
  }

  /**
   * Set the options in the sol selector.
   */
  function updateSolSelector() {
    var mir = parseInt($("#mir").val(), 10);
    var month = parseInt($("#mars-month").val(), 10);
    var n = solsInMonth(mir, month);
    $('#sol28').css('display', (28 <= n) ? 'block' : 'none');
    var $sol = $('#sol');
    if ($sol.val() > n) {
      $sol.val(n);
    }
  }

  /**
   * Update the sol of the week and mir to match the selected Mars date.
   *
   * @param {object} dtMars
   */
  function setMarsDerivedValues(dtMars) {
    // Set the month name.
    $("#mars-month-name").html(dtMars.monthName);

    // Set the sol name.
    $("#sol-name").html(dtMars.solName);

    // Set the sol of the week number.
    $("#sol-of-week").html(appendOrdinalSuffix(dtMars.solOfWeek));

    // Set the sol of the mir number.
    $("#sol-of-mir").html(appendOrdinalSuffix(dtMars.solOfMir));
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////

  $(function() {
    // Check we are on the datetime converter page.
    var $converter = $('#converter');
    if (!$converter.length) {
      return;
    }

    // We are GO for launch.
    initConverter();
  });

})(jQuery);
