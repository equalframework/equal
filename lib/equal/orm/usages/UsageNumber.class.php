<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;

class UsageNumber extends Usage {

    public function getConstraints(): array {
        switch($this->getSubtype()) {
            case 'boolean':
                return [
                    'not_integer' => [
                        'message'   => 'Value is not a boolean.',
                        'function'  =>  function($value) {
                            if(!preg_match('/^[0-1]$/', (string) $value)) {
                                return false;
                            }
                            return true;
                        }
                    ]
                ];
            case 'integer':
                return [
                    'not_integer' => [
                        'message'   => 'Value is not an integer.',
                        'function'  =>  function($value) {
                            $len = intval($this->getLength());
                            // if length is empty, default to 18 (max)
                            $len = ($len)?$len:18;
                            if(!preg_match('/^[+-]?[0-9]{0,'.$len.'}$/', (string) $value)) {
                                return false;
                            }
                            return true;
                        }
                    ]
                ];
            case 'real':
                /*
                    supports:
                        1
                        2.3
                        999.12
                        0.00
                */
                return [
                    'not_real' => [
                        'message'   => 'Value does not comply with real number format.',
                        'function'  =>  function($value) {
                            // expected len format is `precision.scale`
                            $scale = $this->getScale();
                            $integers = $this->getPrecision() - $scale;
                            if(preg_match('/^[+-]?[0-9]{0,'.$integers.'}(\.[0-9]{1,'.$scale.'})?$/', (string) $value)) {
                                return false;
                            }
                            return true;
                        }
                    ]
                ];
            case 'hexadecimal':
                return [
                    'broken_usage' => [
                        'message'   => 'Number does not match usage or length constraint.',
                        'function'  =>  function($value) {
                            $len = intval($this->getLength());
                            // if length is empty, default to 255 (max)
                            $len = ($len)?$len:255;
                            if(preg_match('/^[0-9A-F]{0,'.$len.'}$/', (string) $value)) {
                                return false;
                            }
                            return true;
                        }
                    ]
                ];
            default:
                return [];
        }
    }

    public function export($value, $lang='en'): string {
        $decimal_length = 0;
        if($this->getSubtype() == 'real') {
            // explode precision.scale
            $parts = explode('.', $this->getLength());
            $decimal_length = isset($parts[1])?$parts[1]:0;
        }
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);
        return number_format($value, $decimal_length, $decimal_separator, $thousands_separator);
    }

}
