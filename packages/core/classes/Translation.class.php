<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
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
                'type'              => 'string',
                'usage'             => 'text/plain:128'
            ],
            'object_field' => [
                'type'              => 'string',
                'usage'             => 'text/plain:128'
            ],
            'object_id' => [
                'type'              => 'integer'
            ],
            'value' => [
                'type'              => 'binary'
            ]
        ];
    }

    public function getIndexes(): array {
        return [
            // translation lookups for specific objects and fields
            ['object_class', 'object_id', 'object_field', 'language'],
            // class-and-language scans, used when exporting translations by object
            ['object_class', 'language', 'object_id', 'object_field']
        ];
    }
}
