<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\data\DataGenerator;

class UsageAmount extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        // single number as length
        if(strpos($this->length_str, '.') === false) {
            // use provided precision, fallback to default
            $this->precision = 10;

            if($this->length > 0) {
                $this->scale = $this->length;
            }
            else {
                // use scale according to subtype
                $map_default = [
                    'money'     => 4,
                    'percent'   => 6,
                    'rate'      => 4
                ];
                $this->scale = $map_default[$this->getSubtype(0)] ?? 2;
            }
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

    public function generateRandomValue(): float {
        switch($this->getSubtype()) {
            case 'money':
            case 'percent':
            case 'rate':
                return DataGenerator::realNumber($this->getPrecision(), $this->getScale());
        }
        return 0;
    }

}
