<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;

class UsageNumeric extends Usage {

    public function getType(): string {
        return 'numeric';
    }

    public function getSqlType(): string {
        $len = $this->getLength();
        switch($this->getSubtype()) {
            case 'integer':
                return ($len)?'int('.$len.')':'int(11)';
            case 'real':
                $parts = explode('.', $len);
                $precision = (isset($parts[0]))?$parts[0]:10;
                $scale = (isset($parts[1]))?$parts[1]:2;
                return 'decimal('.$precision.','.$scale.')';
            case 'hexadecimal':
                return ($len)?'varchar('.$len.')':'varchar(255)';
        }
        return 'varchar(255)';
    }

    public function getConstraints(): array {
        switch($this->getSubtype()) {
            case 'integer':
                return [
                    'broken_usage' => [
                        'message'   => 'Number does not match usage or length constraint.',
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
                    'broken_usage' => [
                        'message'   => 'Number does not match usage or length constraint.',
                        'function'  =>  function($value) {
                            $len = intval($this->getLength());
                            // expected len format is `precision.scale`
                            $parts = explode('.', $len);
                            $scale = isset($parts[1])?$parts[1]:0;
                            $integers = $parts[0] - $scale;
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
