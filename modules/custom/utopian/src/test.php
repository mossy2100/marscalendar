<?php
require "util.php";
require "StarDateTime.php";
require "MarsDateTime.php";

//$x = new StarDateTime(2000, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new StarDateTime(1970, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new StarDateTime(1975, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//
//$x = new StarDateTime(2017, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new StarDateTime(1979, 12, 31, 23, 59, 59, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new StarDateTime(1980, 1, 1, 0, 0, 0, "UTC");
//echo $x->leapSecondsSoFar() . "\n";

//$x = new StarDateTime(2017, 7, 31, 13, 35, 22, "UTC");
//$x = StarDateTime::fromJulianDate(2308804.25046); // Darian

// Midnight MSD -94130
// $x = StarDateTime::fromJulianDate(2308804.25087);
// Equal to:
// $x = new StarDateTime(1609, 3, 10, 18, 1, 15, "UTC");

//$x = StarDateTime::fromJulianDate(2308804.282733746); // http://www-mars.lmd.jussieu.fr

// Why is this one day ahead?
//$x = StarDateTime::fromJulianDate(2308805.27819); // Martiana

// Zero point of Mars Sol Date
//$jd1 = 2405522.0;

// First calculated Mars vernal equinox.
//$jd1 = 2405668.690;

// Wikipedia page for Timekeeping on Mars.
// 31 Jul 2017, 13:35:22
$dt_gen = new StarDateTime(2017, 7, 31, 13, 35, 22, 'UTC');

function test_msd($jd1) {
  echo 'Julian date 1: ' . $jd1 . "\n";

  $x = StarDateTime::fromJulianDate($jd1);

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

  $diff = abs($jd2 - $jd1) * StarDateTime::SECONDS_PER_DAY;
  echo 'Error: ' . $diff . " seconds\n";

  $jdtai = $x->toJulianDate('TAI');
  echo 'Julian date (TAI): ' . $jdtai . "\n";

  $diff = ($jdtai - $jd1) * StarDateTime::SECONDS_PER_DAY;
  $diff = round($diff * 1e3) / 1e3;
  echo 'Difference: ' . $diff . " seconds\n";

  $jdtt = $x->toJulianDate('TT');
  echo 'Julian date (TT): ' . $jdtt . "\n";

  $diff = ($jdtt - $jd1) * StarDateTime::SECONDS_PER_DAY;
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
  echo 'Time of sol (24h): ' . StarDateTime::padDigits($hour) . ':' .
    StarDateTime::padDigits($minute) . ':' . StarDateTime::padDigits($second) .
    "\n";

  // How many seconds is this?
  $ss = $time_of_sol * SECONDS_PER_SOL;
  $s = round($ss * 1e3) / 1e3;
  echo 'Time of sol (seconds): ' . $s . "\n";

  // How many days is this?
  $d = $ss / StarDateTime::SECONDS_PER_DAY;
  echo 'Time of sol (days): ' . $d . "\n";

  $jd0 = $jd1 - $d;
  return $jd0;
}

function test_constants() {
  echo "SECONDS_PER_SOL = " . SECONDS_PER_SOL . "\n";
  echo "DAYS_PER_SOL = " . DAYS_PER_SOL . "\n";
  echo "SOLS_PER_MIR = " . SOLS_PER_MIR . "\n";
  echo "DAYS_PER_MIR = " . DAYS_PER_MIR . "\n";
  // Get the JD for the start of the epoch.
  $jd = 2405668.690 - 141 * DAYS_PER_MIR;
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
    'gradient'    => $m,
    'intercept'   => $c,
    'function'    => $f,
//    'differences' => $diffs,
    'max diff'    => $max_diff,
  ];
}

/**
 * Extract the vernal equinoxes from the HTML file.
 * @todo use the original text!
 */
function get_mars_vernal_equinoxes() {
  $rows = file(__DIR__ . '/vernal-equinoxes-of-mars.htm');
  $values = [];
  $xs = [];
  $ys = [];
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

    $dt = new StarDateTime($dts, 'UTC');
    $jd = $dt->toJulianDate();

    $values[] = [$mir, $jd];
    $xs[] = $mir;
    $ys[] = $jd;
    $points[] = "$mir,$jd";

    $mir++;
  }

  echo implode(',', $xs) . "\n";
  echo implode(',', $ys) . "\n";

  $csv = "mir,julianDate\n" . implode("\n", $points);
  file_put_contents(__DIR__ . '/mars-vernal-equinoxes.csv', $csv);

  $mirs = implode(",", $xs);
  $jds = implode(",", $ys);
  file_put_contents(__DIR__ . '/mirs-and-julian-dates.csv', "$mirs\n\n$jds");

  return $values;
}

//test_msd();
$values = get_mars_vernal_equinoxes();

$info = calc_line_of_best_fit($values);
var_dump($info);

//$jd_start = $info['intercept'];
//echo "Estimated JD for epoch start: " . $jd_start . "\n";
//echo "Estimated date time for epoch start: " . StarDateTime::fromJulianDate($jd_start) . "\n";
//echo "Estimated max error: " . $info['max diff'] . "\n";
//$jd_min = $jd_start - $info['max diff'];
//echo "Estimated min JD: " . $jd_min . "\n";
//echo "Estimated min date time: " . StarDateTime::fromJulianDate($jd_min) . "\n";
//$jd_max = $jd_start + $info['max diff'];
//echo "Estimated max JD: " . $jd_max . "\n";
//echo "Estimated max date time: " . StarDateTime::fromJulianDate($jd_max) . "\n";
//
//$jd0 = test_msd($jd_start);
//test_msd($jd0);

//test_constants();
//
//$jd = $dt_gen->toJulianDate();
//test_msd($jd);
