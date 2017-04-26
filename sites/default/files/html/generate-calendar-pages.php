<?php

echo "<p>Viewing the calendar in the familiar format of one page per month helps to visualise how a mir is
  divided into months and weeks, and how the weeks align with months. Note how the final week of each
  quarter is a short, 6-sol week. The intercalary sol at the end of a long mir (Tucana 28) is shown in red.</p>
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

foreach ($months as $i => $month) {
  if ($i == 24) {
    $day28 = "<td class='intercalary'>28</td>";
  }
  elseif ($i % 6 == 0) {
    $day28 = "<td class='empty'>&nbsp;</td>";
  }
  else {
    $day28 = "<td>28</td>";
  }

  if ($i <= 6) {
    $class = 'spring';
  }
  elseif ($i <= 12) {
    $class = 'summer';
  }
  elseif ($i <= 18) {
    $class = 'autumn';
  }
  else {
    $class = 'winter';
  }

  echo "
<table class='calPage $class'>
  <thead>
  <tr>
    <th class='monthName' colspan='7'>$i. $month</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td class='monthIcon' colspan='7'><img src='/sites/default/files/month-icons/$month.png'></td>
  </tr>
  <tr class='daysOfWeek'>
    <th>S</th>
    <th>P</th>
    <th>E</th>
    <th>M</th>
    <th>J</th>
    <th>V</th>
    <th>D</th>
  </tr>
  <tr>
    <td>1</td>
    <td>2</td>
    <td>3</td>
    <td>4</td>
    <td>5</td>
    <td>6</td>
    <td>7</td>
  </tr>
  <tr>
    <td>8</td>
    <td>9</td>
    <td>10</td>
    <td>11</td>
    <td>12</td>
    <td>13</td>
    <td>14</td>
  </tr>
  <tr>
    <td>15</td>
    <td>16</td>
    <td>17</td>
    <td>18</td>
    <td>19</td>
    <td>20</td>
    <td>21</td>
  </tr>
  <tr>
    <td>22</td>
    <td>23</td>
    <td>24</td>
    <td>25</td>
    <td>26</td>
    <td>27</td>
    $day28
  </tr>
  </tbody>
</table>
";
}
