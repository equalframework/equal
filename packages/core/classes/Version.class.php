<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

/**
 * @deprecated
 */
class Version extends Model {

    public static function getColumns() {
        return [
            /* override state field */
            'state' => [
                'type'              => 'string',
                'selection'         => ['draft', 'version']
            ],

            'object_class' => [
                'type'              => 'string'
            ],

            'object_id' => [
                'type'              => 'integer'
            ],

            'serialized_value' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
            ],

            'lang' => [
                'type'              => 'string'
            ]
        ];
    }
}