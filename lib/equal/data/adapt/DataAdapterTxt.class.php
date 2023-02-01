<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\data\DataFormatter;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;
use equal\html\HTMLToText;
use equal\locale\Locale;

class DataAdapterTxt extends DataAdapter {

   /**
     * This Adapter is for adaptOut only (txt has no formal input format)
     *
     */

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage, $lang='en') {
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        switch($type) {
            case 'amount':
                return self::amountToTxt($value, $usage, $lang);
            case 'number':
                switch($subtype) {
                    case 'boolean':
                        return self::booleanToTxt($value, $usage, $lang);
                    case 'natural':
                    case 'integer':
                        return self::integerToTxt($value, $usage, $lang);
                    case 'hexadecimal':
                        return is_null($value)?null:hexdec($value);
                    case 'real':
                        return self::floatToTxt($value, $usage, $lang);
                }
                break;
            case 'text':
                    switch($subtype) {
                        case 'html':
                            return HTMLToText::convert($value, false);
                        case 'markdown':
                            // #todo - convert from MD
                        case 'wiki':
                            // #todo - convert from wikitext
                    }
                break;
            case 'time':
                return self::timeToTxt($value, $usage, $lang);
            case 'date':
                switch($subtype) {
                    case 'plain':
                        return self::dateToTxt($value, $usage, $lang);
                        case 'year':
                        // date/year:4 (integer 0-9999)
                    case 'month':
                }
                break;
            case 'datetime':
                return self::datetimeToTxt($value, $usage, $lang);
            case 'image':
            case 'binary':
                return self::binaryToTxt($value, $usage, $lang);

        }
        return parent::adaptOut($value, $usage);
    }



    /* private methods holding the adaptation logic */


    private static function booleanToTxt($value, $usage, $lang) {
        if($value) {
            $value = Locale::get_term('core', 'bool.true', 'true', $lang);
        }
        else {
            $value = Locale::get_term('core', 'bool.false', 'false', $lang);
        }
        return $value;
    }


    private static function floatToTxt($value, $usage, $lang) {
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);
        $decimal_length = intval($usage->getLength());
        return number_format($value, $decimal_length, $decimal_separator, $thousands_separator);
    }

    private static function integerToTxt($value, $usage, $lang) {
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
        return number_format($value, 0, '', $thousands_separator);
    }

    private static function binaryToTxt($value, $usage, $lang) {
        return base64_encode($value);
    }

    private static function datetimeToTxt($value, $usage, $lang) {
        $date_format = Locale::get_format('core', 'date.short', 'YY-MM-DD', $lang);
        $time_format = Locale::get_format('core', 'time.medium', 'HH:mm', $lang);
        return DataFormatter::format($value, 'date', $date_format.' '.$time_format);
    }

    private static function timeToTxt($value, $usage, $lang) {
        // convert time to a timestamp relative to current date at midnight
        $time = strtotime('today midnight');
        $time += $value;
        $time_format = Locale::get_format('core', 'time.medium', 'HH:mm', $lang);
        return DataFormatter::format($time, 'time', $time_format);
    }

    private static function dateToTxt($value, $usage, $lang) {
        $date_format = Locale::get_format('core', 'date.full', 'dddd DD MMMM YYYY', $lang);
        return DataFormatter::format($value, 'date', $date_format);
    }

    private static function amountToTxt($value, $usage, $lang) {
        $decimal_length = intval($usage->getLength());
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);

        $number = number_format($value, $decimal_length, $decimal_separator, $thousands_separator);

        switch($usage->getSubtype()) {
            case 'money':
                $currency_symbol_position = Locale::get_format('core', 'currency.symbol_position', 'before', $lang);
                $currency_symbol_separator = Locale::get_format('core', 'currency.symbol_separator', '', $lang);
                $currency_symbol = Setting::get_value('core', 'units', 'currency', '$');
                if($currency_symbol_position == 'before') {
                    $number = $currency_symbol.$currency_symbol_separator.$number;
                }
                else {
                    $number = $number.$currency_symbol_separator.$currency_symbol;
                }
                break;
            case 'percent':
                $number = $number.'%';
                break;
            case 'rate':
                break;
        }
        return $number;
    }
}