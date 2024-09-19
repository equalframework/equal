<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\data\DataGenerator;

class UsageNumber extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        $subtype = $this->getSubtype(0);

        if($subtype == 'boolean') {
            $this->length = 1;
        }
        elseif($subtype == 'real') {
            // single number as length: use it as scale and set to default precision
            if(strpos($this->length_str, '.') === false) {
                $this->precision = 10;
                $this->scale = max($this->length, 2);
                $this->length = $this->precision;
            }
            else {
                // use provided precision, fallback to default
                $this->precision = max($this->precision, 2);
                $this->scale = max($this->scale, 2);
            }
        }
        else {
            if($this->length == 0) {
                $this->length = 10;
            }
        }
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
                            return preg_match('/^[+-]?[0-9]{0,'.$this->getLength().'}$/', (string) $value);
                        }
                    ],
                    'too_low' => [
                        'message'   => 'Value is too low.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value >= $this->getMin();
                        }
                    ],
                    'too_high' => [
                        'message'   => 'Value is too high.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value <= $this->getMax();
                        }
                    ]
                ];
            case 'natural':
                return [
                    'not_natural' => [
                        'message'   => 'Value is not a natural number.',
                        'function'  =>  function($value) {
                            return preg_match('/^[0-9]{0,'.$this->getLength().'}$/', (string) $value);
                        }
                    ],
                    'too_low' => [
                        'message'   => 'Value is too low.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value >= $this->getMin();
                        }
                    ],
                    'too_high' => [
                        'message'   => 'Value is too high.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value <= $this->getMax();
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
                            $integers = $this->getPrecision();
                            $decimals = $this->getScale();
                            return preg_match('/^[+-]?[0-9]{0,'.$integers.'}(\.[0-9]{1,'.$decimals.'})?$/', (string) $value);
                        }
                    ],
                    'too_low' => [
                        'message'   => 'Value is too low.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value >= $this->getMin();
                        }
                    ],
                    'too_high' => [
                        'message'   => 'Value is too high.',
                        'function'  =>  function($value) {
                            return ($this->getMin() === 0 && $this->getMax() === 0) || $value <= $this->getMax();
                        }
                    ]
                ];
            default:
                return [];
        }
    }

    /**
     * @return bool|float|int|string|null
     */
    public function generateRandomValue() {
        switch($this->getSubtype(0)) {
            case 'boolean':
                return DataGenerator::boolean();
            case 'integer':
                if($this->getMin() === 0 && $this->getMax() === 0) {
                    return DataGenerator::integerByLength($this->getLength());
                }

                return DataGenerator::integer($this->getMin(), $this->getMax());
            case 'real':
                if($this->getMin() === 0 && $this->getMax() === 0) {
                    return DataGenerator::realNumberByLength($this->getLength(), $this->getScale());
                }

                return DataGenerator::realNumber($this->getMin(), $this->getMax(), $this->getScale());
            case 'hexadecimal':
                return DataGenerator::hexadecimal($this->getLength());
        }

        return 0;
    }

}
