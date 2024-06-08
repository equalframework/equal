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
        $scale = parent::getScale();
        $precision = parent::getPrecision();
        $length = parent::getLength();
        // single number as length: use it as scale
        if($length && $length == $precision) {
            $scale = $length;
        }
        else {
            $map_default = [
                'money'     => 4,
                'percent'   => 6,
                'rate'      => 4
            ];
            $subtype = $this->getSubtype();
            $scale = $map_default[$subtype] ?? 2;
        }
        return $scale;
    }

    public function getPrecision(): int {
        $precision = parent::getPrecision();
        $length = parent::getLength();
        // single number as length means 'scale': use default precision
        if($length == $precision) {
            $precision = 10;
        }
        else {
            // use provided precision, fallback to default
            $precision = ($precision)?$precision:2;
        }
        return $precision;
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
                    $decimals = $this->getScale();
                    $integers = $this->getPrecision();
                    switch($this->getSubtype()) {
                        case 'money':
                        case 'percent':
                        case 'rate':
                            return preg_match('/^[+-]?[0-9]{0,'.$integers.'}(\.[0-9]{0,'.$decimals.'})?$/', (string) $value);
                    }
                    return true;
                }
            ]
        ];
    }

}
