/**
 * @file
 * Live Mars clock showing the current Utopian datetime.
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
   * Start a clock, updating it once per microsol.
   *
   * @param {HTMLElement} root
   *   The clock element.
   */
  const startClock = (root) => {
    const field = (name) => root.querySelector(`[data-utopian="${name}"]`);
    const timeZone = field('clock-time-zone');
    const datetime = field('clock-datetime');
    const monthName = field('clock-month-name');
    const solName = field('clock-sol-name');
    const solOfWeek = field('clock-sol-of-week');
    const solOfMir = field('clock-sol-of-mir');

    const update = () => {
      const now = Drupal.utopian.timestampToUtopian(Date.now(), parseInt(timeZone.value, 10) || 0);
      datetime.textContent = Drupal.utopian.formatUtopian(now);
      monthName.textContent = now.monthName;
      solName.textContent = now.solName;
      setOrdinal(solOfWeek, now.solOfWeek);
      setOrdinal(solOfMir, now.solOfMir);
    };

    timeZone.addEventListener('change', update);
    update();
    setInterval(update, Drupal.utopian.MS_PER_MICROSOL);
  };

  /**
   * Start each Mars clock on the page.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.utopianClock = {
    attach(context) {
      once('utopian-clock', '.utopian-clock', context).forEach(startClock);
    },
  };
})(Drupal, once);
