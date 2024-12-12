<?php
/**
 * Created by PhpStorm at 2017-09-08T11:56
 *
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

function test_msd($jd1) {
  echo 'Julian date 1: ' . $jd1 . "\n";

  $x = EarthDateTime::fromJulianDate($jd1);

  $y = $x->getYear();
  $mon = $x->getMonth();
  $d = $x->getDay();
  $h = $x->getHour();
  $min = $x->getMinute();
  $s = $x->getSecond();
  $tz = $x->getTimezone();

  echo 'year: ' . $y . "\n";
  echo 'month: ' . $mon . "\n";
  echo 'day: ' . $d . "\n";
  echo 'hour: ' . $h . "\n";
  echo 'minute: ' . $min . "\n";
  echo 'second: ' . $s . "\n";
  echo 'timezone: ' . $tz->getName() . "\n";

  $jd2 = $x->toJulianDate();
  echo 'Julian date 2: ' . $jd2 . "\n";

  $diff = abs($jd2 - $jd1) * EarthDateTime::SECONDS_PER_DAY;
  echo 'Error: ' . $diff . " seconds\n";

  $jdtai = $x->toJulianDate('TAI');
  echo 'Julian date (TAI): ' . $jdtai . "\n";

  $diff = ($jdtai - $jd1) * EarthDateTime::SECONDS_PER_DAY;
  $diff = round($diff * 1e3) / 1e3;
  echo 'Difference: ' . $diff . " seconds\n";

  $jdtt = $x->toJulianDate('TT');
  echo 'Julian date (TT): ' . $jdtt . "\n";

  $diff = ($jdtt - $jd1) * EarthDateTime::SECONDS_PER_DAY;
  $diff = round($diff * 1e3) / 1e3;
  echo 'Difference: ' . $diff . " seconds\n";

  $msd = mars_sol_date($x);
  echo 'Mars Sol Date: ' . $msd . "\n";

  $mars_sol_count = floor($msd);
  echo 'Mars Sol Number: ' . $mars_sol_count . "\n";

  $time_of_sol = $msd - $mars_sol_count;
  $mils = $time_of_sol * 1000;
  $mils = round($mils * 1e3) / 1e3;
  echo 'Time of sol (mils): ' . $mils . "\n";

  $hours = $time_of_sol * 24;
  $hour = floor($hours);
  $minutes = ($hours - $hour) * 60;
  $minute = floor($minutes);
  $seconds = ($minutes - $minute) * 60;
  $second = floor($seconds);
  echo 'Time of sol (24h): ' . EarthDateTime::pad($hour) . ':' .
    EarthDateTime::pad($minute) . ':' . EarthDateTime::pad($second) .
    "\n";

  // How many seconds is this?
  $ss = $time_of_sol * MarsDateTime::SECONDS_PER_SOL;
  $s = round($ss * 1e3) / 1e3;
  echo 'Time of sol (seconds): ' . $s . "\n";

  // How many days is this?
  $d = $ss / EarthDateTime::SECONDS_PER_DAY;
  echo 'Time of sol (days): ' . $d . "\n";

  $jd0 = $jd1 - $d;
  return $jd0;
}

function test_constants() {
  echo "SECONDS_PER_SOL = " . MarsDateTime::SECONDS_PER_SOL . "\n";
  echo "DAYS_PER_SOL = " . MarsDateTime::DAYS_PER_SOL . "\n";
  echo "SOLS_PER_MIR = " . MarsDateTime::SOLS_PER_MIR . "\n";
  echo "DAYS_PER_MIR = " . MarsDateTime::DAYS_PER_MIR . "\n";
  // Get the JD for the start of the epoch.
  $jd = 2405668.690 - 141 * MarsDateTime::DAYS_PER_MIR;
  echo "epoch start JD = " . $jd . "\n";
  //  test_msd($jd);
}

/**
 * Calculate line of best fit.
 *
 * @param array $values
 *   Array of arrays, each with two floats for x and y.
 *   e.g. [ [2.1, 5.0], [4.0, 10.2], [5.9, 15.1] ...]
 *
 * @return
 */
function calc_line_of_best_fit($values) {
  // Calculate average values for x and y.
  $n = count($values);
  $x_sum = 0;
  $y_sum = 0;
  foreach ($values as list($x, $y)) {
    $x_sum += $x;
    $y_sum += $y;
  }
  $x_avg = $x_sum / $n;
  $y_avg = $y_sum / $n;

  // Calculate gradient.
  $num = 0;
  $den = 0;
  foreach ($values as list($x, $y)) {
    $x_diff = $x - $x_avg;
    $y_diff = $y - $y_avg;
    $num += $x_diff * $y_diff;
    $den += pow($x_diff, 2);
  }
  $m = ($den == 0) ? 0 : ($num / $den);

  // Calculate intercept.
  $c = $y_avg - ($m * $x_avg);

  // Get function.
  $f = function ($x2) use ($m, $c) {
    return $m * $x2 + $c;
  };

  // Calculate differences.
  $diffs = [];
  $max_diff = NULL;
  $csv = "mir,diff\n";
  foreach ($values as list($x, $y)) {
    $y2 = $f($x);
    $dy = $y2 - $y;
    $diffs[] = $dy;
    $abs_dy = abs($dy);
    if (is_null($max_diff) || $abs_dy > $max_diff) {
      $max_diff = $abs_dy;
    }
    $csv .= "$x,$dy\n";
  }

  file_put_contents(__DIR__ . '/differences.csv', $csv);

  // Return information about the line of best fit.
  return [
    'gradient'  => $m,
    'intercept' => $c,
    'function'  => $f,
    //    'differences' => $diffs,
    'max diff'  => $max_diff,
  ];
}

/**
 * Extract the vernal equinoxes from the HTML file.
 *
 * @todo Get Meeus text and use that as the reference.
 */
function get_mars_vernal_equinoxes() {
  $rows = file(__DIR__ . '/vernal-equinoxes-of-mars.htm');
  $values = [];
  $mir_values = [];
  $jd_values = [];
  $dt_strings = [];
  $points = [];
  $mir = 141;

  foreach ($rows as $row) {
    preg_match_all("|<td>([^<]*)</td>|", $row, $matches);
    //    var_dump($matches);

    $date = trim($matches[1][0]);
    $date_parts = explode(' ', $date);
    $date_string = $date_parts[0] . '-' . substr($date_parts[1], 0, 3) . '-' . $date_parts[2];

    $time = trim($matches[1][1]);
    $dts = "$date_string $time";
    echo $dts . "\n";

    $dt = new EarthDateTime($dts, 'UTC');
    $jd = $dt->toJulianDate();

    $values[] = [$mir, $jd];
    $dt_strings[] = $dts;
    $mir_values[] = $mir;
    $jd_values[] = $jd;
    $points[] = "$mir,$jd,$dts";

    $mir++;
  }

  echo implode(',', $mir_values) . "\n";
  echo implode(',', $jd_values) . "\n";

  $csv = "mir,julianDate,datetime\n" . implode("\n", $points);
  file_put_contents(__DIR__ . '/mars-vernal-equinoxes.csv', $csv);

  $mirs = implode(",", $mir_values);
  $jds = implode(",", $jd_values);
  file_put_contents(__DIR__ . '/mirs-and-julian-dates.csv', "$mirs\n\n$jds");

  return $values;
}
