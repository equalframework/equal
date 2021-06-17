<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Log extends Model {

    public static function getColumns() {
        return [
            'action' => [
                'type'              => 'string',
                'description'       => 'The name of the action performed on targeted object (\'create\', \'update\', \'delete\', or a custom value).',
                'required'          => true
            ],
            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'domain'            => ['id', '<', 3],                
                'required'          => true
            ],
            'object_class' => [
                'type'              => 'string',
                'description'       => "Class of entity this entry is related to."
            ],
            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the targeted object (of given class)."
            ]
        ];
    }
}