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

    /*
        date/time (ISO 8601 date and time)
        date/plain (ISO 8601 @ 00:00:00UTC)
        date/year:4 (integer 0-9999)
        date/month	(integer 1-12, ISO-8601)
        date/weekday.mon (ISO-8601: 1 to 7, 1 is Monday)
        date/weekday.sun (0 to 6, 0 is Sunday)
        date/monthday (ISO-8601)
        date/yearweek
        date/yearday (ISO-8601)
        datetime (ISO 8601)
     */
    public function getConstraints(): array {
        $constraints = [
                'invalid_type' => [
                    'message'   => 'Value is incompatible with type datetime.',
                    'function'  =>  function($value) {
                        return (gettype($value) == 'integer');
                    }
                ]
            ];
        switch($this->getSubtype(0)) {
            case 'day':
                $constraints[] = [
                    'invalid_date' => [
                        'message'   => 'Malformed day value.',
                        'function'  =>  function($value) {
                            // 2 digits, from 1 to 31
                            return ($value >= 0 && $value <= 31);
                        }
                    ]
                ];
                break;
            case 'month':
                $constraints[] = [
                    'invalid_date' => [
                        'message'   => 'Malformed month value.',
                        'function'  =>  function($value) {
                            // 2 digits, from 1 to 12
                            return ($value >= 0 && $value <= 12);
                        }
                    ]
                ];
                break;
            case 'year':
                $constraints[] = [
                    'invalid_date' => [
                        'message'   => 'Malformed year value.',
                        'function'  =>  function($value) {
                            // 4 digits, from 0 to 9999
                            return ($value >= 0 && $value <= 9999);
                        }
                    ]
                ];
                break;
            default:
                $constraints[] = [
                    'invalid_date' => [
                        'message'   => 'Malformed date or unknown format.',
                        'function'  =>  function($value) {
                            return ($value <= PHP_INT_MAX && $value >= 0);
                        }
                    ]
                ];
        }
        return $constraints;
    }

    public function generateRandomValue(): int {
        switch($this->getSubtype(0)) {
            case 'day':
                return mt_rand(1, 31);
            case 'weekday.mon':
                return mt_rand(1, 7);
            case 'weekday.sun':
                return mt_rand(0, 6);
            case 'month':
                return mt_rand(1, 12);
            case 'year':
                return mt_rand(1, 9999);
            case 'yearweek':
                return mt_rand(1, 52);
            case 'yearday':
                return mt_rand(1, 365);
            case 'time':
            case 'plain':
            default:
                $start_timestamp = strtotime('1970-01-01 00:00:00');
                $end_timestamp = time();

                return mt_rand($start_timestamp, $end_timestamp);
        }
    }

}
