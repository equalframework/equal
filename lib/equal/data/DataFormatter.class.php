<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data;

/**
 *
 * This class is intended to be used as a helper for back-end generated documents,
 * and holds a single static method for formatting a value, according to a given usage.
 *
 */


class DataFormatter {


  public static function format($value, $usage, $locale=DEFAULT_LANG) {
    switch($usage) {
      case 'phone':
        return self::format_phone($value);
      case 'uri/urn.iban':
      case 'iban':
        return self::format_iban($value);
      case 'bic':
        return self::format_bic($value);
      case 'scor':
        return self::format_scor($value);
    }
    return $value;
  }

  /**
   * SCOR is the belgian Structured Communication Reference used in bank payments.
   *
  */
  private static function format_scor($ref) {
    return '+++'.substr($ref, 0, 3).'/'.substr($ref, 3, 4).'/'.substr($ref, 7, 5).'+++';
  }

  /**
   * BIC (Business Identifier Code) codes are 8 to 11 chars long and follow the iso norm ISO 9362:2014.
   *
   */
  private static function format_bic($bic) {
    return strtoupper(str_replace([' ', '.', '-'], '', $bic));
  }

  /**
   * IBAN codes start with 2 letters (ISO 3166 country code) + variable length series of digits or chars
   * min. is 15 chars long, max. is 31 chars long
   */
  private static function format_iban($iban) {
    $iban = strtoupper(str_replace([' ', '.', '-'], '', $iban));
    $len = strlen($iban);
    if($len <= 16) {
      return substr($iban, 0, 4).' '.substr($iban, 4, 4).' '.substr($iban, 8, 4).' '.substr($iban, 12);
    }
    if($len <= 20) {
      return substr($iban, 0, 4).' '.substr($iban, 4, 4).' '.substr($iban, 8, 4).' '.substr($iban, 12, 4).' '.substr($iban, 16);
    }
    if($len <= 24) {
      return substr($iban, 0, 4).' '.substr($iban, 4, 4).' '.substr($iban, 8, 4).' '.substr($iban, 12, 4).' '.substr($iban, 16, 4).' '.substr($iban, 20);
    }
    if($len <= 28) {
      return substr($iban, 0, 4).' '.substr($iban, 4, 4).' '.substr($iban, 8, 4).' '.substr($iban, 12, 4).' '.substr($iban, 16, 4).' '.substr($iban, 20, 4).' '.substr($iban, 24);
    }
    return substr($iban, 0, 4).' '.substr($iban, 4, 4).' '.substr($iban, 8, 4).' '.substr($iban, 12, 4).' '.substr($iban, 16, 4).' '.substr($iban, 20, 4).' '.substr($iban, 24, 4).' '.substr($iban, 28);
  }

  /**
   *
   *
   * +32489532419  12    +32 489 53 24 19
   * +3286434407   11    +32 86 43 44 07
   * +326736276    10    +32 673 62 76
   * +32488100      9    +32 48 81 00
   */
  private static function format_phone($phone) {

    // phone prefixes (ITU-T E.123 & E.164)
    static $prefixes = [
      // belgium
      '32'  => [
        'prefixes'  => ['499','498','497','496','495','494','493','492','491','490','489','488','487','486','485','484','483','479','478','477','476','475','474','473','472','471','470','468','467','466','465','461','460','456','455','89','87','86','85','84','83','82','81','80','71','69','68','67','65','64','63','61','60','59','58','57','56','55','54','53','52','51','50','19','16','15','14','13','12','11','10','9','4','3','2'],
        'nsn'       => 9
      ],
      // france
      '33'  => [
        'prefixes'  => ['1','2','3','4','5','6','7','8','9'],
        'nsn'       => 9
      ]

      /*
      United-States & Canada
      1
      Egypt
      20
      South-Africa
      27
      Greece
      30
      Netherlands
      31
      Belgium
      32
      France
      33
      Spain
      34
      Hungary
      36
      Italy
      39
      Romania
      40
      Switzerland
      41
      Austria
      43
      United-Kingdom
      44
      Denmark
      45
      Sweden
      46
      Norway
      47
      Poland
      48
      Germany
      49
      Peru
      51
      Mexico
      52
      Cuba
      53
      Argentina
      54
      Brazil
      55
      Chile
      56
      Easter-Island
      56
      Colombia
      57
      Venezuela
      58
      Malaysia
      60
      Australia
      61
      Indonesia
      62
      Philippines
      63
      New-Zealand
      64
      Pitcairn
      Islands
      64
      Singapore
      65
      Thailand
      66
      Japan
      81
      Korea-South
      82
      Vietnam
      84
      China
      86
      Turkey
      90
      India
      91
      Pakistan
      92
      Afghanistan
      93
      Sri-Lanka
      94
      Myanmar
      95
      Iran
      98

      */

    ];

    $phone = str_replace(' ', '', $phone);

    if(strlen($phone) < 6) return $phone;

    $is_international = false;
    $national_prefix = '';
    $local_prefix = '';

    if(substr($phone, 0, 1) == '+') {
      $is_international = true;
      $phone = substr($phone, 1);
    }
    else if(substr($phone, 0, 2) == '00') {
      $is_international = true;
      $phone = substr($phone, 2);
    }

    if(!$is_international && substr($phone, 0, 1) == '0') {
      // national notation
      // -> format based on length
    }
    else {
      for($i = 1; $i <= 4; ++$i) {
        $prefix = substr($phone, 0, $i);
        if(isset($prefixes[$prefix])) {
          $test = substr($phone, $i);
          foreach($prefixes[$prefix]['prefixes'] as $local) {
            if(strpos($test, $local) === 0 && strlen($test)+1 >= $prefixes[$prefix]['nsn']) {
              $is_international = true;
              $phone = substr($test, strlen($local));
              $national_prefix = $prefix;
              $local_prefix = $local;
              break 2;
            }
          }
        }
      }
    }

    switch(strlen($phone)) {
      case 13:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 3),
              substr($phone, 3, 3),
              substr($phone, 6, 3),
              substr($phone, 9));
        break;
      case 12:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 3),
              substr($phone, 3, 3),
              substr($phone, 6, 3),
              substr($phone, 9));
        break;
      case 11:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 2),
              substr($phone, 2, 3),
              substr($phone, 5, 3),
              substr($phone, 8));
        break;
      case 10:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 4),
              substr($phone, 4, 2),
              substr($phone, 6, 2),
              substr($phone, 8));
        break;
      case 9:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 1),
              substr($phone, 1, 3),
              substr($phone, 4, 2),
              substr($phone, 6, 2));
        break;
      case 8:
        $to = sprintf("%s %s %s %s",
              substr($phone, 0, 2),
              substr($phone, 2, 2),
              substr($phone, 4, 2),
              substr($phone, 6, 2));
        break;
      case 7:
        $to = sprintf("%s %s %s",
              substr($phone, 0, 3),
              substr($phone, 3, 2),
              substr($phone, 5));
        break;
      case 6:
      default:
          $to = sprintf("%s %s %s",
                substr($phone, 0, 2),
                substr($phone, 2, 2),
                substr($phone, 4));
    }

    if(strlen($local_prefix)) {
      $to = $local_prefix.' '.$to;
    }

    if(strlen($national_prefix)) {
      $to = $national_prefix.' '.$to;
    }

    if($is_international) {
        $to = '+'.$to;
    }

    return $to;
  }
}