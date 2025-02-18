<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Translation extends Model {

    public static function getColumns() {
        return [
            'language' => [
                'type'              => 'string',
                'usage'             => 'language/iso-639:2'
            ],
            'object_class' => [
                'type'              => 'string'
            ],
            'object_field' => [
                'type'              => 'string'
            ],
            'object_id' => [
                'type'              => 'integer'
            ],
            'value' => [
                'type'              => 'binary'
            ]
        ];
    }
}