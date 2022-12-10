<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\locale\Locale;
use core\setting\Setting;

class UsageDate extends Usage {

    /**
     */
    public function getConstraints(): array {
        switch($this->getSubtype()) {
            case 'day':
                return [
                    'invalid_amount' => [
                        'message'   => 'Malformed amount or size overflow.',
                        'function'  =>  function($value) {
                            // 2 digits, from 1 to 31
                            return ($value >= 0 && $value <= 31);
                        }
                    ]
                ];
            case 'month':
                return [
                    'invalid_amount' => [
                        'message'   => 'Malformed amount or size overflow.',
                        'function'  =>  function($value) {
                            // 2 digits, from 1 to 12
                            return ($value >= 0 && $value <= 12);
                        }
                    ]
                ];
            case 'year':
                return [
                    'invalid_amount' => [
                        'message'   => 'Malformed amount or size overflow.',
                        'function'  =>  function($value) {
                            // 4 digits, from 0 to 9999
                            return ($value >= 0 && $value <= 9999);
                        }
                    ]
                ];
            default:
                return [
                    'invalid_amount' => [
                        'message'   => 'Malformed amount or size overflow.',
                        'function'  =>  function($value) {
                            return ($value <= PHP_INT_MAX && $value >= 0);
                        }
                    ]
                ];
        }
    }

    public function export($value, $lang='en'): string {
       // #todo
        return $value;
    }

}
