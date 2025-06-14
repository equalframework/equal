<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use \equal\orm\Model;

class Change extends Model {

    public static function getDescription() {
        return "Details of the modifications associated with a log (diff-type payload).";
    }

    public function getTable() {
        return 'core_change';
    }

    public static function getColumns() {
        return [
            'log_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\\Log',
                'description'       => "Reference to the parent log.",
                'required'          => true
            ],

            'description' => [
                'type'              => 'string',
                'usage'             => 'text/plain.small',
                'description'       => "Free description of the modification made."
            ],

            'diff' => [
                'type'              => 'string',
                'usage'             => 'text/json',
                'description'       => "JSON content of the change, containing only the modified fields."
            ]
        ];
    }

    public function getUnique() {
        return [
            [ 'log_id' ]
        ];
    }

}
