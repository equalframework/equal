<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageArray extends Usage {


    /**
     *
     */
    public function getConstraints(): array {
        return [

            'not_array' => [
                'message'   => 'Value is not an array.',
                'function'  =>  function($value) {
                    return is_array($value);
                }
            ]

        ];
    }

    public function generateRandomValue(): array {
        return [];
    }

}
