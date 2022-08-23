<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;
use core\setting\Setting;

class UsageAmount extends Usage {

    public function getType(): string {
        return 'amount';
    }

    public function getSqlType(): string {
        $precision = 10;
        $scale = intval($this->getLength());
        switch($this->getSubtype()) {
            case 'money':
                // default to decimal(13,4)
                $scale = ($scale)?$scale:4;
                $precision = 17 - $scale;
                break;
            case 'percent':
                // default to decimal(7,6)
                $scale = ($scale)?$scale:6;
                $precision = $scale + 1;
                break;
            case 'rate':
                // default to decimal(10,4)
                $scale = ($scale)?$scale:4;
                $precision = 14 - $scale;
                break;
        }
        return 'decimal('.$precision.','.$scale.')';
    }

    public function validate($value): bool {
        $len = $this->getLength();
        switch($this->getSubtype()) {
            case 'money':
            case 'percent':
            case 'rate':
                // expected len format is single integer
                if(preg_match('/^[+-]?[0-9]{0,15}}(\.?[0-9]{0,4})$/', (string) $value)) {
                    throw new \Exception(serialize(["broken_usage" => "Number is not a real or does not match length constraint."]), QN_ERROR_INVALID_PARAM);
                }
                break;
        }
        return true;
    }

    public function export($value, $lang=DEFAULT_LANG): string {
        $decimal_length = intval($this->getLength());
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);

        $number = number_format($value, $decimal_length, $decimal_separator, $thousands_separator);

        switch($this->getSubtype()) {
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
