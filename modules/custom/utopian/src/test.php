<?php
require "util.php";
require "mars-functions.php";
require "EarthDateTime.php";
require "MarsDateTime.php";

//$x = new EarthDateTime(2000, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new EarthDateTime(1970, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new EarthDateTime(1975, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//
//$x = new EarthDateTime(2017, 1, 1, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new EarthDateTime(1979, 12, 31, 23, 59, 59, "UTC");
//echo $x->leapSecondsSoFar() . "\n";
//
//$x = new EarthDateTime(1980, 1, 1, 0, 0, 0, "UTC");
//echo $x->leapSecondsSoFar() . "\n";

//$x = new EarthDateTime(2017, 7, 31, 13, 35, 22, "UTC");
//$x = EarthDateTime::fromJulianDate(2308804.25046); // Darian

// Midnight MSD -94130
// $x = EarthDateTime::fromJulianDate(2308804.25087);
// Equal to:
// $x = new EarthDateTime(1609, 3, 10, 18, 1, 15, "UTC");

//$x = EarthDateTime::fromJulianDate(2308804.282733746); // http://www-mars.lmd.jussieu.fr

// Why is this one day ahead?
//$x = EarthDateTime::fromJulianDate(2308805.27819); // Martiana

// Zero point of Mars Sol Date
//$jd1 = 2405522.0;

// First calculated Mars vernal equinox.
//$jd1 = 2405668.690;

// Wikipedia page for Timekeeping on Mars.
// 31 Jul 2017, 13:35:22
$dt_gen = new EarthDateTime([2017, 7, 31, 13, 35, 22], 'UTC');

//test_msd();
$values = get_mars_vernal_equinoxes();

$info = calc_line_of_best_fit($values);
var_dump($info);

//$jd_start = $info['intercept'];
//echo "Estimated JD for epoch start: " . $jd_start . "\n";
//echo "Estimated date time for epoch start: " . EarthDateTime::fromJulianDate($jd_start) . "\n";
//echo "Estimated max error: " . $info['max diff'] . "\n";
//$jd_min = $jd_start - $info['max diff'];
//echo "Estimated min JD: " . $jd_min . "\n";
//echo "Estimated min date time: " . EarthDateTime::fromJulianDate($jd_min) . "\n";
//$jd_max = $jd_start + $info['max diff'];
//echo "Estimated max JD: " . $jd_max . "\n";
//echo "Estimated max date time: " . EarthDateTime::fromJulianDate($jd_max) . "\n";
//
//$jd0 = test_msd($jd_start);
//test_msd($jd0);

//test_constants();
//
//$jd = $dt_gen->toJulianDate();
//test_msd($jd);
