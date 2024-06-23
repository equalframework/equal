<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;

class UsageNumber extends Usage {

    /**
     * Adds support for shortcut notation number/real:5 (equivalent to number/real:10.5)
     */
    public function getScale(): int {
        $scale = parent::getScale();
        $precision = parent::getPrecision();
        $length = parent::getLength();
        if($this->getSubtype(0) == 'real') {
            // single number as length: use it as scale
            if($length && $length == $precision) {
                $scale = $length;
            }
            else {
                // use provided scale, fallback to default scale
                $scale = ($scale)?$scale:2;
            }
        }
        return $scale;
    }

    public function getPrecision(): int {
        $precision = parent::getPrecision();
        $length = parent::getLength();
        if($this->getSubtype(0) == 'real') {
            // single number as length means 'scale': use default precision
            if($length == $precision) {
                $precision = 10;
            }
            else {
                // use provided scale, fallback to default scale
                $precision = ($precision)?$precision:2;
            }
        }
        return $precision;
    }

    public function getLength(): int {
        $precision = parent::getPrecision();
        $length = parent::getLength();
        if($this->getSubtype(0) == 'real') {
            // single number as length means 'scale': use default precision
            if($length == $precision) {
                $length = 10;
            }
            else {
                // use provided scale, fallback to default scale
                $length = ($length)?$length:2;
            }
        }
        return $length;
    }

    public function getConstraints(): array {
        switch($this->getSubtype(0)) {
            case 'boolean':
                return [
                    'not_boolean' => [
                        'message'   => 'Value is not a boolean.',
                        'function'  =>  function($value) {
                            return preg_match('/^[0-1]$/', (string) intval($value));
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
                            return preg_match('/^[+-]?[0-9]{0,'.$len.'}$/', (string) $value);
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
                            $decimals = $this->getScale();
                            $integers = $this->getPrecision();
                            return preg_match('/^[+-]?[0-9]{0,'.$integers.'}(\.[0-9]{1,'.$decimals.'})?$/', (string) $value);
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
                            return preg_match('/^[0-9A-F]{0,'.$len.'}$/', (string) $value);
                        }
                    ]
                ];
            default:
                return [];
        }
    }

}
