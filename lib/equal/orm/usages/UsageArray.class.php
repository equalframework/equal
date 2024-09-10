<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
