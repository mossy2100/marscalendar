<?php
echo "
<p>Viewing the calendar in the familiar format of one page per month helps to visualise how a mir is
  divided into months and weeks, and how the weeks align with months. Note how the final week of
  each quarter is a short, 6-sol week.</p>

<p>The intercalary sol at the end of a long mir (Tucana 28) is shown in bold, and the nominal dates
  for the major astronomical events each mir are highlighted. The actual dates of astronomical
  events can vary slightly from these (although usually by not more than 1 sol) due to the
  intercalation rules not keeping the calendar in precise alignment with Mars&apos; orbit. Credit
  goes to Tom Gangale for calculating these dates.</p>

<table>
  <caption>Annual Astronomical Events (Nominal Dates)</caption>
  <thead>
  <tr>
    <th>Event</th>
    <th>Date</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>Northern vernal/southern autumnal equinox</td>
    <td class=\"special-sol vernal-equinox\">Phoenix 1</td>
  </tr>
  <tr>
    <td>Aphelion</td>
    <td class=\"special-sol aphelion\">Monoceros 12</td>
  </tr>
  <tr>
    <td>Northern summer/southern winter solstice</td>
    <td class=\"special-sol summer-solstice\">Volans 27</td>
  </tr>
  <tr>
    <td>Northern autumnal/southern vernal equinox</td>
    <td class=\"special-sol autumnal-equinox\">Draco 11</td>
  </tr>
  <tr>
    <td>Perihelion</td>
    <td class=\"special-sol perihelion\">Aquila 12</td>
  </tr>
  <tr>
    <td>Northern winter/southern summer solstice</td>
    <td class=\"special-sol winter-solstice\">Vulpecula 14</td>
  </tr>
  </tbody>
</table>
";

$months = [
  1 => 'Phoenix',
  'Cetus',
  'Dorado',
  'Lepus',
  'Columba',
  'Monoceros',
  'Volans',
  'Lynx',
  'Camelopardalis',
  'Chameleon',
  'Hydra',
  'Corvus',
  'Centaurus',
  'Draco',
  'Lupus',
  'Apus',
  'Pavo',
  'Aquila',
  'Vulpecula',
  'Cygnus',
  'Delphinus',
  'Grus',
  'Pegasus',
  'Tucana',
];

$dayNames = [
  1 => 'Phobosol',
  2 => 'Earthsol',
  3 => 'Mercurisol',
  4 => 'Jupitersol',
  5 => 'Venusol',
  6 => 'Deimosol',
  7 => 'Sunsol',
];

foreach ($months as $m => $month) {
  // Add classes for quarters.
  if ($m <= 6) {
    $qtr_class = 'spring';
  }
  elseif ($m <= 12) {
    $qtr_class = 'summer';
  }
  elseif ($m <= 18) {
    $qtr_class = 'autumn';
  }
  else {
    $qtr_class = 'winter';
  }
  
  echo "<table class='calPage $qtr_class'>\n";
  echo "<thead>\n";
  echo "  <tr>\n";
  echo "    <th class='monthName' colspan='7'>$m. $month</th>\n";
  echo "  </tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";
  echo "  <tr>\n";
  echo "    <td class='monthIcon' colspan='7'><img src='/sites/default/files/month-icons/$month.png'></td>\n";
  echo "  </tr>\n";

  // Days of the week header.
  echo "  <tr class='daysOfWeek squares'>\n";
  for ($dow = 1; $dow <= 7; $dow++) {
    echo "    <th>" . $dayNames[$dow][0] . "</th>\n";
  }
  echo "  </tr>\n";

  // Date squares.
  for ($w = 1; $w <= 4; $w++) {
    echo "  <tr class='squares'>\n";
    for ($dow = 1; $dow <= 7; $dow++) {
      $classes = [];

      $sol = ($w - 1) * 7 + $dow;
      $td = $sol;

      // Add classes for special sols.
      if ($m == 1 && $sol == 1) {
        $classes[] = 'special-sol';
        $classes[] = 'vernal-equinox';
      }
      elseif ($m == 6 && $sol == 12) {
        $classes[] = 'special-sol';
        $classes[] = 'aphelion';
      }
      elseif ($m == 6 && $sol == 28) {
        $classes[] = 'empty';
        $td = '&nbsp;';
      }
      elseif ($m == 7 && $sol == 27) {
        $classes[] = 'special-sol';
        $classes[] = 'summer-solstice';
      }
      elseif ($m == 12 && $sol == 28) {
        $classes[] = 'empty';
        $td = '&nbsp;';
      }
      elseif ($m == 14 && $sol == 11) {
        $classes[] = 'special-sol';
        $classes[] = 'autumnal-equinox';
      }
      elseif ($m == 18 && $sol == 12) {
        $classes[] = 'special-sol';
        $classes[] = 'perihelion';
      }
      elseif ($m == 18 && $sol == 28) {
        $classes[] = 'empty';
        $td = '&nbsp;';
      }
      elseif ($m == 19 && $sol == 14) {
        $classes[] = 'special-sol';
        $classes[] = 'winter-solstice';
      }
      elseif ($m == 24 && $sol == 28) {
        $classes[] = 'special-sol';
        $classes[] = 'intercalary';
      }

      echo "    <td";
      if ($classes) {
        echo ' class="' . implode(' ', $classes) . '"';
      }
      echo ">$td</td>\n";
    }

    echo "  </tr>\n";
  }

  echo "</tbody>\n";
  echo "</table>\n";
}
