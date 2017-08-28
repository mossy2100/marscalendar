<?php
/**
 * Created by PhpStorm at 2017-08-28T05:18
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

require "util.php";
require "StarDateTime.php";
require "MarsDateTime.php";

// NVE
$nve = MarsDateTime::northernVernalEquinox(0);

echo "Mars NVE 0\n";

$jd_nve = $nve->toJulianDate();
echo "Julian Date: " . $jd_nve . "\n";
echo "Datetime: " . $nve->getDateTime() . "\n";

echo "MSD: " . $nve->getMarsSolDate() . "\n";

$mtc = $nve->getMTC();
echo "MTC of the NVE, sols: " . $mtc . "\n";

$mtc_days = (1 - $mtc) * MarsDateTime::DAYS_PER_SOL;
echo "MTC of the NVE, days: " . $mtc_days . "\n";

// Epoch
$jd_epoch = $jd_nve + $mtc_days;
$epoch = MarsDateTime::fromJulianDate($jd_epoch);

echo "Mars Epoch Begin (the MTC midnight before NVE 0)\n";

echo "Julian Date of epoch start: " . $jd_epoch . "\n";
echo "Datetime of epoch start: " . $epoch->getDateTime() . "\n";

echo "MSD: " . $epoch->getMarsSolDate() . "\n";

$mtc_epoch = $epoch->getMTC();
echo "MTC of the epoch start, sols: " . $mtc_epoch . "\n";
$mils = round($mtc_epoch * 1e6) / 1e3;
echo " = M:$mils\n";
