// Toolkit.inc:
// Provides a variety of handy functions, typically for working with strings.

function Trim(str) {
  // Trims spaces from the start and end of a string:

  // check we have a value:
  if (str == null || str == "") {
    return "";
  }

  // make a new String for the result:
  var result = new String(str);

  // trim spaces from the start of result:
  while (result.charAt(0) == " ") {
    result = result.substr(1, result.length - 1);
  }

  // trim spaces from the end of result:
  while (result.charAt(result.length - 1) == " ") {
    result = result.substr(0, result.length - 1);
  }

  return result
}

// functions to add characters to start of a string until desired length reached:
function PadLeft(value, width, chPad) {
  // convert to a string:
  var result = value + "";
  // add pad characters until desired width is reached:
  while (result.length < width) {
    result = chPad + result;
  }
  return result;
}

// functions to add characters to end of a string until desired length reached:
function PadRight(value, width, chPad) {
  // convert to a string:
  var result = value + "";
  // add pad characters until desired width is reached:
  while (result.length < width) {
    result = result + chPad;
  }
  return result;
}

// commonly used in time/date displays:
function TwoDigits(n) {
  if (n < 0) {
    return "-" + PadLeft(-n, 2, "0");
  }
  else {
    return PadLeft(n, 2, "0");
  }
}

function ThreeDigits(n) {
  if (n < 0) {
    return "-" + PadLeft(-n, 3, "0");
  }
  else {
    return PadLeft(n, 3, "0");
  }
}

function FourDigits(n) {
  if (n < 0) {
    return "-" + PadLeft(-n, 4, "0");
  }
  else {
    return PadLeft(n, 4, "0");
  }
}

// handy window functions:

function Maximize() {
  ns4 = (document.layers) ? true : false
  ie4 = (document.all) ? true : false

  window.moveTo(0, 0);
  if (ns4) {
    window.outerWidth = screen.availWidth;
    window.outerHeight = screen.availHeight;
  }
  else if (ie4) {
    window.resizeTo(screen.availWidth, screen.availHeight);
  }
}

function Centralize(width, height) {
  window.moveTo((screen.availWidth - width) / 2, (screen.availHeight - height) / 2);
}
