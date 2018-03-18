<?php
/**
 * Created by PhpStorm at 2017-09-07T20:41
 *
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

require "util.php";
require "mars-functions.php";
require "EarthDateTime.php";
require "MarsDateTime.php";

get_mars_vernal_equinoxes();

$f = fopen('mars-vernal-equinoxes.csv', 'r');
$row = fgetcsv($f);
//var_dump($row);

while (!feof($f)) {
  $row = fgetcsv($f);
  $mir = $row[0];
  $jd = $row[1];
  $edts = $row[2];
  $edt = new EarthDateTime($edts, 'UTC');

  if (/*$edt->getYear() >= 1972 &&*/ $edt->getYear() <= 2017) {
    // Calculate deltaT = TT-UTC (seconds).
    $mdt = new MarsDateTime($edt->getTimestamp());
    $msd = $mdt->getMarsSolDate();
    $mtc = $mdt->getMTC();

    echo "Earth datetime = " . $edt->__toString() . "\n";
    echo "Mars datetime = " . $mdt->formatDatetime() . "\n";
    echo "Mir = " . $mir . "\n";
    echo "JD_UTC = " . $jd . "\n";
    echo "MSD = " . $msd . "\n";
    echo "MTC = " . $mtc . "\n";

    $hrs = $mtc * 24;
    $hr = floor($hrs);
    $mins = ($hrs - $hr) * 60;
    $min = floor($mins);
    $secs = ($mins - $min) * 60;
    $sec = floor($secs);
    echo "MTC = " . EarthDateTime::pad($hr) . ':' . EarthDateTime::pad($min) . ':'
      . EarthDateTime::pad($sec) . "\n";

    echo "\n";

    if ($mir == 210) {
      break;
    }
  }
}


// If I use the start of mir 210 as the epoch marker...
//Mir = 210
//JD_UTC = 2453070.18542
//Datetime = 2004-03-05 16:27:00 +00:00 UTC
//MSD = 46275.9981683
//MTC = 0.998168323145
//MTC = 23:57:21

// The start of mir 0 is

$n_sols = MarsDateTime::solsInMir(0) + MarsDateTime::solsInMir(209);

