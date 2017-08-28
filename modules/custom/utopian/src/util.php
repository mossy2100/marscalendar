<?php
/**
 * Created by PhpStorm at 2017-08-28T01:44
 * @author Shaun Moss (shaun@astromultimedia.com, @mossy2100)
 */

/**
 * Converts a version number in the form "x.yyy.zzz" into a float (x.yyyzzz).
 * If it's already a float, e.g. 5.3, it will zeropad the minor version number to thousandths.
 * Enables versions to be compared as numbers.
 *
 * Examples:
 *    5.3 => 5.003
 *   '5.3' => 5.003
 *   '5.10' => 5.010
 *   '5.3.1' => 5.003001
 *
 * @param string $version
 *
 * @return float
 */
function version_to_float($version) {
  $parts = explode('.', strval($version));
  $maj = (int) $parts[0];
  $min = isset($parts[1]) ? (int) $parts[1] : 0;
  $rev = isset($parts[2]) ? (int) $parts[2] : 0;
  return $maj + ($min / 1000) + ($rev / 1000000);
}

/**
 * Get the current PHP version as a float.
 *
 * @return float
 */
function php_version() {
  static $php_ver = NULL;
  // Get the current PHP version as a float, if not already done:
  if (!isset($php_ver)) {
    $php_ver = version_to_float(phpversion());
  }
  return $php_ver;
}

/**
 * Checks if the PHP version is equal to the specified version or higher.
 *
 * @param int|float|string $min_ver
 *
 * @return bool
 */
function php_version_at_least($min_ver) {
  return php_version() >= version_to_float($min_ver);
}
