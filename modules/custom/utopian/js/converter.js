/**
 * @file
 * Datetime converter between the Gregorian and Utopian calendars.
 */

((Drupal, once) => {
  /**
   * Set an element's content to a number followed by its superscript ordinal suffix.
   *
   * @param {HTMLElement} el
   * @param {number} n
   */
  const setOrdinal = (el, n) => {
    const sup = document.createElement('sup');
    sup.textContent = Drupal.utopian.ordinalSuffix(n);
    el.replaceChildren(String(n), sup);
  };

  /**
   * Add zero-padded numeric options to a select element.
   *
   * @param {HTMLSelectElement} select
   * @param {number} max
   */
  const addOptions = (select, max) => {
    for (let i = 1; i <= max; i++) {
      select.add(new Option(Drupal.utopian.padDigits(i, 2), String(i)));
    }
  };

  /**
   * Hide the options above a limit, and lower the selection if it's above the limit.
   *
   * @param {HTMLSelectElement} select
   * @param {number} limit
   */
  const limitOptions = (select, limit) => {
    Array.from(select.options).forEach((option) => {
      const hidden = Number(option.value) > limit;
      option.hidden = hidden;
      option.disabled = hidden;
    });
    if (Number(select.value) > limit) {
      select.value = String(limit);
    }
  };

  /**
   * A datetime converter bound to one converter element.
   */
  class Converter {
    /**
     * @param {HTMLElement} root
     *   The converter element.
     */
    constructor(root) {
      const field = (name) => root.querySelector(`[data-utopian="${name}"]`);
      this.earth = {
        year: field('year'),
        month: field('earth-month'),
        day: field('day'),
        time: field('earth-time'),
        timeZone: field('earth-time-zone'),
        monthName: field('earth-month-name'),
        dayName: field('day-name'),
        dayOfWeek: field('day-of-week'),
        dayOfYear: field('day-of-year'),
      };
      this.mars = {
        mir: field('mir'),
        month: field('mars-month'),
        sol: field('sol'),
        time: field('mars-time'),
        timeZone: field('mars-time-zone'),
        monthName: field('mars-month-name'),
        solName: field('sol-name'),
        solOfWeek: field('sol-of-week'),
        solOfMir: field('sol-of-mir'),
      };
      this.currentYear = null;
      this.currentMir = null;

      addOptions(this.earth.month, 12);
      addOptions(this.earth.day, 31);
      addOptions(this.mars.month, Drupal.utopian.MONTHS_PER_MIR);
      addOptions(this.mars.sol, Drupal.utopian.SOLS_PER_LONG_MONTH);

      field('reset').addEventListener('click', (event) => {
        event.preventDefault();
        this.reset();
      });
      this.bindEarthEvents(root);
      this.bindMarsEvents(root);
      this.reset();
    }

    /**
     * Update the Mars fields when any Earth field changes.
     *
     * @param {HTMLElement} root
     */
    bindEarthEvents(root) {
      // Normalise the year and time before the shared handler reads them.
      this.earth.year.addEventListener('change', () => {
        const year = parseInt(this.earth.year.value, 10);
        this.earth.year.value = String(Number.isNaN(year) ? this.currentYear : year);
      });
      this.earth.time.addEventListener('change', () => {
        this.earth.time.value = Drupal.utopian.formatEarthTime(this.earth.time.value);
      });

      root.querySelectorAll('.earth-date').forEach((el) => {
        el.addEventListener('change', () => {
          this.limitDays();
          const dtEarth = this.getEarth();
          this.showEarthDerived(dtEarth);
          this.setMars(Drupal.utopian.timestampToUtopian(dtEarth.valueOf(), this.marsTimeZone()));
        });
      });
    }

    /**
     * Update the Earth fields when any Mars field changes.
     *
     * @param {HTMLElement} root
     */
    bindMarsEvents(root) {
      // Normalise the mir and time before the shared handler reads them.
      this.mars.mir.addEventListener('change', () => {
        const mir = parseInt(this.mars.mir.value, 10);
        this.mars.mir.value = String(Number.isNaN(mir) ? this.currentMir : mir);
      });
      this.mars.time.addEventListener('change', () => {
        this.mars.time.value = Drupal.utopian.formatMarsTime(this.mars.time.value);
      });

      root.querySelectorAll('.mars-date').forEach((el) => {
        el.addEventListener('change', () => {
          this.limitSols();
          const dtMars = this.getMars();
          this.showMarsDerived(dtMars);
          this.setEarth(new Date(Drupal.utopian.utopianToTimestamp(dtMars)));
        });
      });
    }

    /**
     * Set both calendars to the current datetime.
     */
    reset() {
      const now = new Date();
      now.setMilliseconds(0);
      this.currentYear = now.getFullYear();
      this.setEarth(now);

      const dtMars = Drupal.utopian.timestampToUtopian(now.valueOf(), this.marsTimeZone());
      this.currentMir = dtMars.mir;
      this.setMars(dtMars);
    }

    /**
     * Check if the Earth fields are in UTC rather than local time.
     *
     * @return {boolean}
     */
    isUtc() {
      return this.earth.timeZone.value === 'utc';
    }

    /**
     * Get the selected Mars time zone.
     *
     * @return {number}
     */
    marsTimeZone() {
      return parseInt(this.mars.timeZone.value, 10) || 0;
    }

    /**
     * Read the Earth datetime from the form.
     *
     * @return {Date}
     */
    getEarth() {
      const year = parseInt(this.earth.year.value, 10);
      const monthIndex = parseInt(this.earth.month.value, 10) - 1;
      const day = parseInt(this.earth.day.value, 10);
      const [hour, minute, second] = this.earth.time.value.split(':').map((part) => parseInt(part, 10));

      // Set the full year separately so that years 0..99 aren't mapped to 1900..1999.
      const dt = new Date(0);
      if (this.isUtc()) {
        dt.setUTCFullYear(year, monthIndex, day);
        dt.setUTCHours(hour, minute, second, 0);
      }
      else {
        dt.setFullYear(year, monthIndex, day);
        dt.setHours(hour, minute, second, 0);
      }
      return dt;
    }

    /**
     * Write an Earth datetime to the form.
     *
     * @param {Date} dtEarth
     */
    setEarth(dtEarth) {
      const utc = this.isUtc();
      this.earth.year.value = String(utc ? dtEarth.getUTCFullYear() : dtEarth.getFullYear());
      this.earth.month.value = String((utc ? dtEarth.getUTCMonth() : dtEarth.getMonth()) + 1);
      this.limitDays();
      this.earth.day.value = String(utc ? dtEarth.getUTCDate() : dtEarth.getDate());
      this.earth.time.value = Drupal.utopian.formatHms(
        utc ? dtEarth.getUTCHours() : dtEarth.getHours(),
        utc ? dtEarth.getUTCMinutes() : dtEarth.getMinutes(),
        utc ? dtEarth.getUTCSeconds() : dtEarth.getSeconds(),
      );
      this.showEarthDerived(dtEarth);
    }

    /**
     * Show the month name, day name, day of the week and day of the year for an Earth datetime.
     *
     * @param {Date} dtEarth
     */
    showEarthDerived(dtEarth) {
      const utc = this.isUtc();
      const month = (utc ? dtEarth.getUTCMonth() : dtEarth.getMonth()) + 1;
      const dayOfWeek = Drupal.utopian.dayOfWeek(dtEarth, utc);
      this.earth.monthName.textContent = Drupal.utopian.gregorianMonthName(month);
      this.earth.dayName.textContent = Drupal.utopian.gregorianDayName(dayOfWeek);
      setOrdinal(this.earth.dayOfWeek, dayOfWeek);
      setOrdinal(this.earth.dayOfYear, Drupal.utopian.dayOfYear(dtEarth, utc));
    }

    /**
     * Limit the day selector to the length of the selected month.
     */
    limitDays() {
      const year = parseInt(this.earth.year.value, 10);
      const month = parseInt(this.earth.month.value, 10);
      limitOptions(this.earth.day, Drupal.utopian.daysInMonth(year, month));
    }

    /**
     * Read the Utopian datetime from the form.
     *
     * @return {object}
     */
    getMars() {
      return Drupal.utopian.makeUtopian(
        parseInt(this.mars.mir.value, 10),
        parseInt(this.mars.month.value, 10),
        parseInt(this.mars.sol.value, 10),
        parseFloat(this.mars.time.value),
        this.marsTimeZone(),
      );
    }

    /**
     * Write a Utopian datetime to the form.
     *
     * @param {object} dtMars
     */
    setMars(dtMars) {
      this.mars.mir.value = String(dtMars.mir);
      this.mars.month.value = String(dtMars.month);
      this.limitSols();
      this.mars.sol.value = String(dtMars.solOfMonth);
      this.mars.time.value = Drupal.utopian.formatMarsTime(dtMars.mils);
      this.mars.timeZone.value = String(dtMars.timeZone);
      this.showMarsDerived(dtMars);
    }

    /**
     * Show the month name, sol name, sol of the week and sol of the mir for a Utopian datetime.
     *
     * @param {object} dtMars
     */
    showMarsDerived(dtMars) {
      this.mars.monthName.textContent = dtMars.monthName;
      this.mars.solName.textContent = dtMars.solName;
      setOrdinal(this.mars.solOfWeek, dtMars.solOfWeek);
      setOrdinal(this.mars.solOfMir, dtMars.solOfMir);
    }

    /**
     * Limit the sol selector to the length of the selected month.
     */
    limitSols() {
      const mir = parseInt(this.mars.mir.value, 10);
      const month = parseInt(this.mars.month.value, 10);
      limitOptions(this.mars.sol, Drupal.utopian.solsInMonth(mir, month));
    }
  }

  /**
   * Initialise the datetime converter.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.utopianConverter = {
    attach(context) {
      once('utopian-converter', '.utopian-converter', context).forEach((root) => new Converter(root));
    },
  };
})(Drupal, once);
