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

    public function getScale(): int {
        $scale = intval($this->getLength());
        switch($this->getSubtype()) {
            case 'money':
                $scale = ($scale)?$scale:4;
                break;
            case 'percent':
                $scale = ($scale)?$scale:6;
                break;
            case 'rate':
                $scale = ($scale)?$scale:4;
                break;
        }
        return $scale;
    }

    /**
     * supports:
     *      123456789.1
     *      1.123456
     *      -2.41
     *      +3.1
     *      0.6
     */
    public function getConstraints(): array {
        return [
            'invalid_amount' => [
                'message'   => 'Malformed amount or size overflow.',
                'function'  =>  function($value) {
                    $scale = $this->getScale();
                    switch($this->getSubtype()) {
                        case 'money':
                        case 'percent':
                        case 'rate':
                            if(preg_match('/^[+-]?[0-9]{0,9}(\.[0-9]{0,'.$scale.'})?$/', (string) $value)) {
                                return false;
                            }
                            break;
                    }
                    return true;
                }
            ]
        ];
    }


    // this has to be called by dataadatper to TXT
    public function export($value, $lang='en'): string {
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
