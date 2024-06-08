<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Log extends Model {

    public static function getColumns() {
        return [
            'action' => [
                'type'              => 'string',
                'required'          => true,
                'description'       => 'The name of the action performed on targeted object (\'create\', \'update\', \'delete\', or any custom value).',
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'required'          => true,
                'description'       => 'User that performed the action.'
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => "Class of entity this entry is related to."
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the targeted object (of given class)."
            ],

            'value' => [
                'type'              => 'string',
                'type'              => 'text/json',
                'description'       => "Changes (new values) made to the object, if any.",
                'help'              => "JSON representation of the new values(diff) of the object (if changes were made)."
            ]

        ];
    }

    public function getUnique() {
        return [
            ['user_id'],
            ['object_class'],
            ['object_id']
        ];
    }
}
