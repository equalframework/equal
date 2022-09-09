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

    public function getType(): string {
        return 'date';
    }

    public function getSqlType(): string {
        $len = $this->getLength();
        $type = 'INT';
        switch($this->getSubtype()) {
            case 'day':
                // 2 digits
                $type = 'TINYINT';
                break;
            case 'month':
                // 2 digits
                $type = 'TINYINT';
                break;
            case 'year':
                // 4 digits
                $type = 'SMALLINT';
                break;
            default:
                // full date (no time)
                $type = 'DATE';
        }
        return $type;
    }

    /**
     */
    public function getConstraints(): array {
        return [
            'invalid_amount' => [
                'message'   => 'Malformed amount or size overflow.',
                'function'  =>  function($value) {
                    switch($this->getSubtype()) {
                        case 'day':
                            // 2 digits, from 1 to 31
                            if($value > 31 || $value <= 0) {
                                return false;
                            }
                            break;
                        case 'month':
                            // 4 digits, from 1 to 12
                            if($value > 12 || $value <= 0) {
                                return false;
                            }
                            break;
                        case 'year':
                            // 4 digits, from 0 to 9999
                            if($value > 9999 || $value < 0) {
                                return false;
                            }
                            break;
                        default:
                            // timestamp, from 0 to PHP_INT_MAX
                            if($value == PHP_INT_MAX || $value < 0) {
                                return false;
                            }
                    }
                    return true;
                }
            ]
        ];
    }

    public function export($value, $lang=DEFAULT_LANG): string {
       // #todo
        return $value;
    }

}
