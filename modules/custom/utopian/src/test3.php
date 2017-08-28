<?php
/**
 * Created by PhpStorm at 2017-08-29T01:02
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

require "util.php";
require "StarDateTime.php";
require "MarsDateTime.php";

// Julian Date for the 1609 NVE.
$jd = 2308806.29606;
echo "Julian Date of 1609 NVE = $jd\n";

$dt_nve = StarDateTime::fromJulianDate($jd);
echo "DT of 1609 NVE = $dt_nve\n";
$dt_nve2 = StarDateTime::fromJulianDate2($jd);
echo "DT of 1609 NVE (method 2) = $dt_nve2\n";

// Number of Julian centuries before 1820.
$cy = (1820 - 1609) / 100;
echo "Num centuries before 1820 = $cy centuries\n";

// Calculate deltaT = TT-UTC (seconds).
$cy2 = pow($cy, 2);
$deltaT = round(-20 + (32 * $cy2));
echo "∆T = TT-UT = $deltaT seconds\n";

// Calculate error in deltaT.
$err = 0.82 * $cy2;
echo "error in ∆T = $err seconds\n";

// Calculate JD(TT).
$jdtt = $jd + $deltaT / StarDateTime::SECONDS_PER_DAY;
echo "JD_TT = $jdtt\n";

// Calculate MSD.
$offset = -2451549.5 + (44796.0 - 0.000962) * MarsDateTime::DAYS_PER_SOL;
echo "Offset = $offset\n";
$msd = ($jdtt + $offset) / MarsDateTime::DAYS_PER_SOL;
echo "MSD = $msd\n";
echo "days per sol = " . MarsDateTime::DAYS_PER_SOL . "\n";

// Calculate MSD (2nd method).
$msd2 = (($jdtt - 2451549.5) / MarsDateTime::DAYS_PER_SOL) + 44796.0 - 0.0009626;
echo "MSD (2nd method) = $msd2\n";

// Get the Mars Sol Number.
$msn = floor($msd);
echo "MSC = $msn\n";

// Get MTC.
$mtc = $msd - $msn;
echo "MTC = $mtc sols\n";

// Calculate number of days to add.
$mtc_d = (1 - $mtc) * MarsDateTime::DAYS_PER_SOL;
$seconds = $mtc_d * 86400;
$minutes = $seconds / 60;
echo "days to add = $mtc_d days = $seconds seconds = $minutes minutes\n";

// Add to original JD.
$jd_epoch = $jd + $mtc_d;
echo "JD midnight = $jd_epoch\n";

// Get the Gregorian date time.
$dt = StarDateTime::fromJulianDate($jd_epoch);
echo "Epoch start = $dt\n";
