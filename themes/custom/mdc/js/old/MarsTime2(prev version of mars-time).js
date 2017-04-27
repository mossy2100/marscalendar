/* Required files:
 Toolkit.js
 */

// to convert Earth time to Mars:
var MILLISECONDS_PER_SOL = 88775244;

// units for decimal clock:
var MILS_PER_SOL = 1000;
var BEATS_PER_MIL = 100;

// units for timeslip clock:
var HOURS_PER_SOL = 24.65979;
var MINUTES_PER_HOUR = 60;
var SECONDS_PER_MINUTE = 60;

// units for stretched clock:
var SPELLS_PER_SOL = 24;
var MOMENTS_PER_SPELL = 60;
var JIFFIES_PER_MOMENT = 60;

// units for modified decimal clocks:
var HORAS_PER_SOL = 25;
var MILS_PER_HORA = 40;

// start date for Viking 1:
//var MARS_START = Date.UTC(1975, 11, 17, 23, 46, 12, 0).valueOf();
// start date for Martian northern vernal equinox in 1609, the year
// Astronomy Novia was published by Johannes Kepler, and also the year
// the telescope was first used for astronomy, by Galileo Galilei:
var MARS_START = Date.UTC(1609, 02, 11, 18, 40, 46, 400).valueOf();

function IsLeapMir(mir)
// returns true if a leap mir:
{
  // if mir is not divisible by 10 then the rule is very simple:
  // it's a leap year if odd, and not a leap year if even:
  if (mir % 10 != 0) {
    return (mir % 2 == 1);
  }
  // now we know mir is divisble by 10, must investigate further:
  // if mir divisible by 1000, then it's a leap mir:
  if (mir % 1000 == 0) {
    return true;
  }
  // if mir divisible by 100 then it's not a leap mir:
  if (mir % 100 == 0) {
    return false;
  }
  // otherwise it is a leap mir:
  return true;
}

function SolsInMir(mir)
// returns number of sols in mir specified by mir:
{
  if (IsLeapMir(mir)) {
    return 669;
  }
  else {
    return 668;
  }
}

function SolsInMonth(month, mir)
// returns number of sols in month, specified by month and mir:
{
  // Vrishika is a special case:
  if (month == 24) {
    if (IsLeapMir(mir)) {
      return 28;
    }
    else {
      return 27;
    }
  }
  else if (month == 6 || month == 12 || month == 18) {
    return 27;
  }
  else {
    return 28;
  }
}

function MarsDate2(milliseconds) {
  // Constructor for MarsDate2 object.
  // input: time in milliseconds (standard return from (new Date()).valueOf())
  // output: object representing Mars date and time

  // if no parameter provided, use current time:
  if (typeof(milliseconds) == 'undefined') {
    milliseconds = (new Date()).valueOf();
  }

  // convert the milliseconds to number of sols since MARS_START:
  var sols = (milliseconds - MARS_START) / MILLISECONDS_PER_SOL;

  // which mir:
  var Mir = 0;
  var MirLen;
  if (sols < 0) // it's a negative mir
  {
    while (sols < 0) {
      Mir--;
      MirLen = SolsInMir(Mir);
      sols += MirLen;
    }
  }
  else // it's 0 or +ve mir:
  {
    MirLen = SolsInMir(Mir)
    // if we have more sols left than there are in current mir,
    // subtract the number of sols in the current mir from the remainder,
    // and go to the next mir:
    while (sols > MirLen) {
      sols -= MirLen;
      Mir++;
      MirLen = SolsInMir(Mir);
    }
  }
  this.Mir = Mir;
  SolNum = sols;
  MarsSolNum = sols;

  // which month:
  // start in Sagittarius (month 1):
  var Month = 1;
  var MonthLen = SolsInMonth(Month, Mir);
  while (sols > MonthLen) {
    // goto next month:
    sols -= MonthLen;
    Month++;
    MonthLen = SolsInMonth(Month, Mir);
  }
  this.Month = Month;

  // get whole number of remaining sols
  var nWholeSols = Math.floor(sols);
  this.Sol = nWholeSols + 1; // add 1 because if there is 0 whole sols left we are in the first sol of the month

  // the fractional part of the sol is whatever's left:
  sols -= nWholeSols;
  this.Frac = sols;
}

function MarsDate2_valueOf(MDate) {
  // ** currently only programmed for +ve mirs:
  // convert MDate to sols:
  var sols = 0;
  for (var mir = 0; mir < MDate.Mir; mir++) {
    sols += SolsInMir(mir);
  }
  sols += 28 * (MDate.Month - 1) + (MDate.Sol - 1) + MDate.Frac;
  // convert to milliseconds:
  return sols * MILLISECONDS_PER_SOL + MARS_START;
}

function MarsMonth(month)
// returns the month name given the month number (1..24):
{
  switch (month) {
    case 1:
      return "Sagittarius";
    case 2:
      return "Lyra";
    case 3:
      return "Capricornus";
    case 4:
      return "Cygnus";
    case 5:
      return "Aquarius";
    case 6:
      return "Pegasus";
    case 7:
      return "Pisces";
    case 8:
      return "Phoenix";
    case 9:
      return "Aries";
    case 10:
      return "Perseus";
    case 11:
      return "Taurus";
    case 12:
      return "Orion";
    case 13:
      return "Gemini";
    case 14:
      return "Columba";
    case 15:
      return "Cancer";
    case 16:
      return "Lynx";
    case 17:
      return "Leo";
    case 18:
      return "Hydra";
    case 19:
      return "Virgo";
    case 20:
      return "Crux";
    case 21:
      return "Libra";
    case 22:
      return "Ursa";
    case 23:
      return "Scorpius";
    case 24:
      return "Draco";
  }
}

function AbbrevMarsMonth(month)
// returns the month name given the month number (1..24):
{
  switch (month) {
    case 1:
      return "Sag";
    case 2:
      return "Dha";
    case 3:
      return "Cap";
    case 4:
      return "Mak";
    case 5:
      return "Aqr";
    case 6:
      return "Kum";
    case 7:
      return "Psc";
    case 8:
      return "Min";
    case 9:
      return "Ari";
    case 10:
      return "Mes";
    case 11:
      return "Tau";
    case 12:
      return "Ris";
    case 13:
      return "Gem";
    case 14:
      return "Mit";
    case 15:
      return "Can";
    case 16:
      return "Kar";
    case 17:
      return "Leo";
    case 18:
      return "Sim";
    case 19:
      return "Vir";
    case 20:
      return "Kan";
    case 21:
      return "Lib";
    case 22:
      return "Tul";
    case 23:
      return "Sco";
    case 24:
      return "Vri";
  }
}

function SolName(nSolOfMonth)
// returns the sol name given the sol number (1..28):
{
  milliseconds = (new Date()).valueOf();
  roundFloorSols = Math.floor((milliseconds - MARS_START) / MILLISECONDS_PER_SOL);
  nSolOfWeek = (roundFloorSols % 7 + 1);

  switch (nSolOfWeek) {
    case 1:
      return "Phobosol";
    case 2:
      return "Deimosol";
    case 3:
      return "Terrasol";
    case 4:
      return "Venusol";
    case 5:
      return "Mercurisol";
    case 6:
      return "Jovisol";
    case 7:
      return "Sunsol";
  }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// functions to format Mars dates and times:

function FormatMarsDate2(MDate) {
  return ThreeDigits(MDate.Mir) + " " + MarsMonth(MDate.Month) + " " + TwoDigits(MDate.Sol);
  //	return ThreeDigits(MDate.Mir) + "-" + AbbrevMarsMonth(MDate.Month) + "-" + TwoDigits(MDate.Sol);
}

function FormatMarsTime2Decimal(MDate) {
  var frac = MDate.Frac * MILS_PER_SOL;
  var Mils = Math.floor(frac);
  frac = (frac - Mils) * BEATS_PER_MIL;
  var Beats = Math.floor(frac);
  return ThreeDigits(Mils) + "." + TwoDigits(Beats);
}

function FormatMarsTime2Timeslip(MDate) {
  var frac = MDate.Frac * HOURS_PER_SOL;
  var Hours = Math.floor(frac);
  frac = (frac - Hours) * MINUTES_PER_HOUR;
  var Minutes = Math.floor(frac);
  frac = (frac - Minutes) * SECONDS_PER_MINUTE;
  var Seconds = Math.floor(frac);
  return TwoDigits(Hours) + ":" + TwoDigits(Minutes) + ":" + TwoDigits(Seconds);
}

function FormatMarsTime2Stretched(MDate) {
  var frac = MDate.Frac * SPELLS_PER_SOL;
  var Spells = Math.floor(frac);
  frac = (frac - Spells) * MOMENTS_PER_SPELL;
  var Moments = Math.floor(frac);
  frac = (frac - Moments) * JIFFIES_PER_MOMENT;
  var Jiffies = Math.floor(frac);
  return TwoDigits(Spells) + ":" + TwoDigits(Moments) + ":" + TwoDigits(Jiffies);
}

function FormatMarsTime2ModifiedDecimal(MDate) {
  var frac = MDate.Frac * HORAS_PER_SOL;
  var Horas = Math.floor(frac);
  frac = (frac - Horas) * MILS_PER_HORA;
  var Mils = Math.floor(frac);
  frac = (frac - Mils) * BEATS_PER_MIL;
  var Beats = Math.floor(frac);
  return TwoDigits(Horas) + ":" + TwoDigits(Mils) + "." + TwoDigits(Beats);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// functions to convert time of day, outputting formatted strings:

// returns a string representation of Mars time given hours:
function ConvertEarthTimeToMarsTime2(nHours) {
  return FormatMarsTime2(nHours * nMilsPerHour);
}

// returns a string representation of Earth time given nMils:
function ConvertMarsTime2ToEarthTime(nMils) {
  return FormatEarthTime(nMils / nMilsPerHour);
}
