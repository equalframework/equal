<?php
namespace core;

use qinoa\orm\Model;

class Log extends Model {

    public static function getColumns() {
        return [
            'action' => [
                'type'              => 'string',
                'description'       => 'The name of the action performed on tergeted object (might be one of the rights `EQ_R_x`, or any custom value)',
                'required'          => true
            ],
            'object_class' => [
                'type'              => 'string',
                'description'       => "Class of entity this entry is related to"
            ],
            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the targeted object"
            ],
            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'required'          => true
            ],
        ];
    }
}