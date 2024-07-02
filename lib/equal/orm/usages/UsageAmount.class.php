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

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        // single number as length: use it as scale and set to default precision
        if(strpos($this->length_str, '.') === false) {
            $this->scale = $this->length;
            $this->precision = 10;
        }
        else {
            // use provided precision, fallback to default
            $this->precision = max($this->precision, 2);

            // use scale according to subtype
            $map_default = [
                'money'     => 4,
                'percent'   => 6,
                'rate'      => 4
            ];
            $subtype = $this->getSubtype(0);
            $this->scale = $map_default[$subtype] ?? 2;
        }
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
