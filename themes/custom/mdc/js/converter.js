/**
 * Created by shaun on 29/4/2017.
 *
 * For the Earth to Mars datetime converter.
 */

var currentMir;

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

    // Reset the datetimes to now.
    resetDatetimes();

    // When the year changes, reformat.
    var $year = $('#year');
    $year.change(function() {
      var year = parseInt($year.val(), 10);
      if (isNaN(year)) {
        // Default to current year.
        year = (new Date()).getFullYear();
      }
      $year.val(year);
    });

    // When the mir changes, reformat.
    var $mir = $('#mir');
    $mir.change(function() {
      var mir = parseInt($mir.val(), 10);
      if (isNaN(mir)) {
        // Default to current mir.
        mir = currentMir;
      }
      $mir.val(mir);
    });

    // When the Earth time changes, reformat.
    var $earthTime = $('#earth-time');
    $earthTime.change(function() {
      $earthTime.val(formatEarthTime($earthTime.val()));
    });

    // When the Mars time changes, reformat.
    var $marsTime = $('#mars-time');
    $marsTime.change(function() {
      $marsTime.val(formatMarsTime($marsTime.val()));
    });

    // Events when any Earth datetime field changes.
    $(".earth-date").change(function() {
      updateDaySelector();
      setDayOfWeekAndYear();
      // Update the Mars datetime.
      var dtEarth = getEarthDatetime();
      var dtMars = gregorian2utopian(dtEarth);
      setMarsDatetime(dtMars);
    });

    // Events when any Mars datetime field changes.
    $(".mars-date").change(function() {
      updateSolSelector();
      setSolOfWeekAndMir();
      // Update the Earth datetime.
      var dtMars = getMarsDatetime();
      var dtEarth = utopian2gregorian(dtMars);
      setEarthDatetime(dtEarth);
    });

    // Reset the datetimes when the reset link is clicked.
    $('#btn-reset-converter').click(resetDatetimes);
  }

  /**
   * Set the Earth and Mars datetimes to now.
   */
  function resetDatetimes() {
    // Get the current datetime.
    var dtEarth = new Date();
    dtEarth.setMilliseconds(0);

    // Set the Earth datetime fields.
    setEarthDatetime(dtEarth);

    // Set the Mars datetime fields.
    var dtMars = gregorian2utopian(dtEarth);
    currentMir = dtMars.mir;
    setMarsDatetime(dtMars);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // Earth datetime functions.

  /**
   * Get the Earth datetime from the form.
   *
   * @returns {Date}
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
    var dtEarth = new Date(year, month - 1, day, hour, minute, second);
    // To add support for years from 0..99, set the year again using setFullYear().
    dtEarth.setFullYear(year);
    return dtEarth;
  }

  /**
   * Set the Earth datetime on the form.
   *
   * @param {Date} dtEarth
   */
  function setEarthDatetime(dtEarth) {
    // Set the mir.
    $("#year").val(dtEarth.getFullYear());

    // Set the month.
    $("#earth-month").val(dtEarth.getMonth() + 1);

    // Set the day.
    $("#day").val(dtEarth.getDate());

    // Set the time.
    $("#earth-time").val(formatEarthTime(dtEarth));

    // Set the day of the week and the year.
    setDayOfWeekAndYear(dtEarth);
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
      label = (i < 10 ? '0' : '') + i + ' (' + GREGORIAN_MONTH_NAMES[i] + ')';
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
   */
  function setDayOfWeekAndYear(dtEarth) {
    if (dtEarth === undefined) {
      dtEarth = getEarthDatetime();
    }

    // Set the day of the week name.
    var dayOfWeek = dtEarth.getDay();
    $("#day-name").html(gregorianDayName(dayOfWeek));

    // Set the day of the week number.
    // JS defines Sunday as day 0, so we add 1, to make Sunday the 1st day of the week.
    // This is usually the case, even though ISO 8601 specifies Monday as day 1 of the week.
    $("#day-of-week").html(appendOrdinalSuffix(dayOfWeek + 1));

    // Set the day of the year number.
    $("#day-of-year").html(appendOrdinalSuffix(dtEarth.getDayOfYear()));
    $("#year2").html(dtEarth.getFullYear());
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

    // Create the result object.
    var dtMars = {
      mir: mir,
      month: month,
      solOfMonth: solOfMonth,
      monthName: utopianMonthName(month),
      solOfWeek: solOfWeek,
      solName: utopianSolName(solOfWeek),
      solOfMir: solOfMir(month, solOfMonth),
      mils: mils
    };

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
    $("#sol").val(dtMars.solOfMonth);

    // Set the time.
    $("#mars-time").val(formatMarsTime(dtMars.mils));

    // Set the sol of the week and the mir.
    setSolOfWeekAndMir(dtMars);
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
      label = (i < 10 ? '0' : '') + i + ' (' + UTOPIAN_MONTH_NAMES[i][1] + ')';
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
   */
  function setSolOfWeekAndMir(dtMars) {
    if (dtMars === undefined) {
      dtMars = getMarsDatetime();
    }

    // Set the sol of the week name.
    $("#sol-name").html(dtMars.solName);

    // Set the sol of the week number.
    $("#sol-of-week").html(appendOrdinalSuffix(dtMars.solOfWeek));

    // Set the sol of the mir number.
    $("#sol-of-mir").html(appendOrdinalSuffix(dtMars.solOfMir));
    $("#mir2").html('M' + dtMars.mir);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////
  // We are GO for launch.
  $(initConverter);

})(jQuery);
