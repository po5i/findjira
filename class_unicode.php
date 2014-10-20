<?php
/* ******************************************************************** */
/* CATALYST PHP Source Code                                             */
/* -------------------------------------------------------------------- */
/* This program is free software; you can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License, or    */
/* (at your option) any later version.                                  */
/*                                                                      */
/* This program is distributed in the hope that it will be useful,      */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        */
/* GNU General Public License for more details.                         */
/*                                                                      */
/* You should have received a copy of the GNU General Public License    */
/* along with this program; if not, write to:                           */
/*   The Free Software Foundation, Inc., 59 Temple Place, Suite 330,    */
/*   Boston, MA  02111-1307  USA                                        */
/* -------------------------------------------------------------------- */
/*                                                                      */
/* Filename:    unicode-defs.php                                        */
/* Author:      Paul Waite                                              */
/* Description: Various functions to help with Unicode conversion etc.  */
/*                                                                      */
/* ******************************************************************** */
/** @package i18n */

// -----------------------------------------------------
/**
* takes a string of unicode entities and converts it to a utf-8 encoded string
* each unicode entitiy has the form &#nnn(nn); n={0..9} and can be displayed by
* utf-8 supporting browsers.  Ascii will not be modified.
* @param string $source String of unicode entities
* @return string The utf-8 encoded string
*/
function unicode_entities_to_utf8_encode($source) {
   $utf8Str = '';
   $entityArray = explode ("&#", $source);
   $size = count ($entityArray);
   for ($i = 0; $i < $size; $i++) {
       $subStr = $entityArray[$i];
       $nonEntity = strstr ($subStr, ';');
       if ($nonEntity !== false) {
           $unicode = intval (substr ($subStr, 0, (strpos ($subStr, ';') + 1)));
           // determine how many chars are needed to reprsent this unicode char
           if ($unicode < 128) {
               $utf8Substring = chr ($unicode);
           }
           else if ($unicode >= 128 && $unicode < 2048) {
               $binVal = str_pad (decbin ($unicode), 11, "0", STR_PAD_LEFT);
               $binPart1 = substr ($binVal, 0, 5);
               $binPart2 = substr ($binVal, 5);

               $char1 = chr (192 + bindec ($binPart1));
               $char2 = chr (128 + bindec ($binPart2));
               $utf8Substring = $char1 . $char2;
           }
           else if ($unicode >= 2048 && $unicode < 65536) {
               $binVal = str_pad (decbin ($unicode), 16, "0", STR_PAD_LEFT);
               $binPart1 = substr ($binVal, 0, 4);
               $binPart2 = substr ($binVal, 4, 6);
               $binPart3 = substr ($binVal, 10);

               $char1 = chr (224 + bindec ($binPart1));
               $char2 = chr (128 + bindec ($binPart2));
               $char3 = chr (128 + bindec ($binPart3));
               $utf8Substring = $char1 . $char2 . $char3;
           }
           else {
               $binVal = str_pad (decbin ($unicode), 21, "0", STR_PAD_LEFT);
               $binPart1 = substr ($binVal, 0, 3);
               $binPart2 = substr ($binVal, 3, 6);
               $binPart3 = substr ($binVal, 9, 6);
               $binPart4 = substr ($binVal, 15);

               $char1 = chr (240 + bindec ($binPart1));
               $char2 = chr (128 + bindec ($binPart2));
               $char3 = chr (128 + bindec ($binPart3));
               $char4 = chr (128 + bindec ($binPart4));
               $utf8Substring = $char1 . $char2 . $char3 . $char4;
           }

           if (strlen ($nonEntity) > 1)
               $nonEntity = substr ($nonEntity, 1); // chop the first char (';')
           else
               $nonEntity = '';

           $utf8Str .= $utf8Substring . $nonEntity;
       }
       else {
           $utf8Str .= $subStr;
       }
   }
   return $utf8Str;
} // unicode_entities_to_utf8_encode

// -----------------------------------------------------
/*
* Returns true if the given string is UTF-8 compliant.
* NB: this doesn't necessarily mean it IS encoded as
* UTF-8 - it might just be an ASCII string.
* @param string $ String to check for compliance
* @return boolean True if string complies with UTF-8 format
*/
function is_utf8($s) {
  for ($i = 0; $i < strlen($s); $i++) {
    $charOrd = ord($s[$i]);
    if ($charOrd < 0x80) {
      continue; # 0bbbbbbb
    }
    elseif (($charOrd & 0xE0) == 0xC0) $n=1; # 110bbbbb
    elseif (($charOrd & 0xF0) == 0xE0) $n=2; # 1110bbbb
    elseif (($charOrd & 0xF8) == 0xF0) $n=3; # 11110bbb
    elseif (($charOrd & 0xFC) == 0xF8) $n=4; # 111110bb
    elseif (($charOrd & 0xFE) == 0xFC) $n=5; # 1111110b
    else {
      # Does not match any model
      return false;
    }
    # n bytes matching 10bbbbbb follow ?
    for ($j = 0; $j < $n; $j++) {
      if ((++$i == strlen($s)) || ((ord($s[$i]) & 0xC0) != 0x80)) {
        return false;
      }
    }
  } // for
  return true;
} // is_utf8

// -----------------------------------------------------
/*
* Return the Unicode ordinal value of a UTF-8 character sequence.
* @param string $c Multi-byte 'string' representing Unicode char
* $return integer The ordinal Unicode code for this character
*/
function utf8ord($c) {
  $uni = 0;
  if (ord($c{0})>=0 && ord($c{0})<=127) {
    $uni = $c{0};
  }
  elseif (ord($c{0})>=192 && ord($c{0})<=223) {
    $uni = (ord($c{0})-192)*64 + (ord($c{1})-128);
  }
  elseif (ord($c{0})>=224 && ord($c{0})<=239) {
    $uni = (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
  }
  elseif (ord($c{0})>=240 && ord($c{0})<=247) {
    $uni = (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
  }
  elseif (ord($c{0})>=248 && ord($c{0})<=251) {
    $uni = (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
  }
  elseif (ord($c{0})>=252 && ord($c{0})<=253) {
    $uni = (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
  }
  elseif (ord($c{0})>=254 && ord($c{0})<=255) {//error
    $uni = false;
  }
  return $uni;
} // utf8ord

// -----------------------------------------------------
/**
* Ensure a string is encoded as UTF-8..
*/
function utf8_ensure($s) {
   return is_utf8($s) ? $s: unicode_entities_to_utf8_encode($s);
} // utf8_ensure

// -----------------------------------------------------
/**
* RFC1738 compliant replacement to PHP's rawurldecode - which
* actually works with unicode (using utf-8 encoding).
* @param string $source The original string
* @return string Unicode-safe rawurldecoded string
*/
function utf8RawUrlDecode($source) {
  $decodedStr = '';
  $pos = 0;
  $len = strlen($source);
  while ($pos < $len) {
    $charAt = substr($source, $pos, 1);
    if ($charAt == '%') {
      $pos++;
      $charAt = substr($source, $pos, 1);
      if ($charAt == 'u') {
        // we got a unicode character
        $pos++;
        $unicodeHexVal = substr($source, $pos, 4);
        $unicode = hexdec($unicodeHexVal);
        $entity = "&#". $unicode . ';';
        $decodedStr .= unicode_entities_to_utf8_encode($entity);
        $pos += 4;
      }
      else {
        // we have an escaped ascii character
        $hexVal = substr($source, $pos, 2);
        $decodedStr .= chr(hexdec ($hexVal));
        $pos += 2;
      }
    }
    else {
      $decodedStr .= $charAt;
      $pos++;
    }
  }
  return $decodedStr;
} // utf8RawUrlDecode

// -----------------------------------------------------
/**
* Replacement for PHP's rawurlencode. This version skips any existing
* sequences of '%xx', which represent already-encoded chars. Also
* uses the multi=byte string functions to preseve unicode chars
* integrity.
* @param string $str The string to URL encode
* @return string The URL-encoded string
*/
Function utf8RawUrlEncode($str) {
  $len = strlen($str);
  $res = "";
  $i = 0;
  $mb = function_exists("mb_substr");
  while ($i < $len) {
    if ($mb) $chk = mb_substr($str, $i, 3);
    else $chk = substr($str, $i, 3);
    if(preg_match("/%[0-9a-f]/i", $chk)) {
      $res .= $chk;
      $i += 3;
    }
    else {
      if ($mb) $charAt = mb_substr($str, $i, 1);
      else $charAt = substr($str, $i, 1);
      $charOrd = ord($charAt);
      if (($charOrd >= 65 && $charOrd <= 90)
       || ($charOrd >= 97 && $charOrd <= 122)
       || ($charOrd >= 48 && $charOrd <= 57)
       || ($charOrd == 33)
       || ($charOrd == 36)
       || ($charOrd == 95)) {
        // this is alphanumeric or $-_.+!*'(), which according
        // to RFC1738 we don't escape
        $res .= $charAt;
      }
      else {
        if (ord($charAt) >= 0x80 && is_utf8($charAt)) {
          $charOrd = utf8ord($charAt);
          $hexValStr = "%u" . sprintf("%04x", $charOrd);
          $res .= $hexValStr;
        }
        elseif ($charOrd > 0) {
          $res .= "%";
          $hexValStr = sprintf("%02x", $charOrd);
          $res .= $hexValStr;
        }
      }
      $i += 1;
    }
  } // while
  return $res;
} // utf8RawUrlEncode


/**
* takes a string of utf-8 encoded characters and converts it to a string of unicode entities
* each unicode entitiy has the form &#nnnnn; n={0..9} and can be displayed by utf-8 supporting
* browsers
* @param $source string encoded using utf-8 [STRING]
* @return string of unicode entities [STRING]
* @access public
*/
/**
* Author: ronen at greyzone dot com
* Taken from php.net comment:
*	http://www.php.net/manual/en/function.utf8-decode.php
**/
function utf8_to_unicode_entities_encode ($source) {
	// array used to figure what number to decrement from character order value
	// according to number of characters used to map unicode to ascii by utf-8
	$decrement[4] = 240;
	$decrement[3] = 224;
	$decrement[2] = 192;
	$decrement[1] = 0;

	// the number of bits to shift each charNum by
	$shift[1][0] = 0;
	$shift[2][0] = 6;
	$shift[2][1] = 0;
	$shift[3][0] = 12;
	$shift[3][1] = 6;
	$shift[3][2] = 0;
	$shift[4][0] = 18;
	$shift[4][1] = 12;
	$shift[4][2] = 6;
	$shift[4][3] = 0;

	$pos = 0;
	$len = strlen ($source);
	$encodedString = '';
	while ($pos < $len) {
		$asciiPos = ord (substr ($source, $pos, 1));
		if (($asciiPos >= 240) && ($asciiPos <= 255)) {
			// 4 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 4);
			$pos += 4;
		}
		else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
			// 3 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 3);
			$pos += 3;
		}
		else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
			// 2 chars representing one unicode character
			$thisLetter = substr ($source, $pos, 2);
			$pos += 2;
		}
		else {
			// 1 char (lower ascii)
			$thisLetter = substr ($source, $pos, 1);
			$pos += 1;
		}

		// process the string representing the letter to a unicode entity
		$thisLen = strlen ($thisLetter);
		$thisPos = 0;
		$decimalCode = 0;
		while ($thisPos < $thisLen) {
			$thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
			if ($thisPos == 0) {
				$charNum = intval ($thisCharOrd - $decrement[$thisLen]);
				$decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
			}
			else {
				$charNum = intval ($thisCharOrd - 128);
				$decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
			}

			$thisPos++;
		}

		if ($decimalCode<128)
			$encodedLetter = chr($decimalCode);
		else if ($thisLen == 1)
			$encodedLetter = "&#". str_pad($decimalCode, 3, "0", STR_PAD_LEFT) . ';';
		else
			$encodedLetter = "&#". str_pad($decimalCode, 5, "0", STR_PAD_LEFT) . ';';

		$encodedString .= $encodedLetter;
	}

	return $encodedString;
}



// -----------------------------------------------------
?>
