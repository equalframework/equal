<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Setting extends Model {

    public static function getName() {
        return 'Configuration parameter';
    }

    public static function getDescription() {
        return "Configurations parameters may be used by any package and provide specfic information about current installation.";
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string', 
                'description'       => 'Unique code of the parameter.'
            ],

            'package' => [
                'type'              => 'string', 
                'description'       => 'Package which the param refers to, if any.',
                'default'           => ''
            ],      

            'section' => [
                'type'              => 'string',
                'description'       => 'Section to ease param retrieval.',
                'required'          => true
            ],

            'title' => [
                'type'              => 'string', 
                'description'       => 'Short title of the parameter.',
                'multilang'         => true
            ],

            'description' => [
                'type'              => 'string',
                'description'       => 'Short memo about the param usage.',
                'multilang'         => true
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => ['boolean', 'integer', 'float', 'string', 'selection'],
                'description'       => 'The format of data stored by the param.',
                'required'          => true
            ],

            'setting_values_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\SettingValue',
                'foreign_field'     => 'setting_id',
                'sort'              => 'desc',
                'order'             => 'user_id',
                'description'       => 'List of values related to the setting.'
            ],

            'setting_choices_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\SettingChoice',
                'foreign_field'     => 'setting_id',
                'description'       => 'List of values related to the setting.'
            ]

        ];
    }

    public function getUnique() {
        return [
            ['package', 'section', 'name']
        ];
    }    
}