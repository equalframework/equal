<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Lang extends Model {

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the language.',
                'multilang'         => true,
                'required'          => true
            ],

            'code' => [
                'type'              => 'string',
                'usage'             => 'language/iso-639:2',
                'description'       => "Lower case alpha-2 language code (iso-639).",
                'required'          => true
            ]
        ];
    }
}