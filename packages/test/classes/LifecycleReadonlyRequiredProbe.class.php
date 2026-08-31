<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace test;

class LifecycleReadonlyRequiredProbe extends Test
{
    public static function getColumns() {
        return [
            'string_short' => [
                'type'     => 'string',
                'usage'    => 'text/plain:9',
                'required' => true,
                'readonly' => true
            ]
        ];
    }
}
