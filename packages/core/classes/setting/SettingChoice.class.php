<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class SettingChoice extends Model {

    public static function getName() {
        return 'Setting choice';
    }

    public static function getDescription() {
        return "Assignable choice for a given setting.";
    }

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the choice, if any.',
                'multilang'         => true
            ],

            'setting_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\Setting',
                'description'       => 'Setting the choice relates to.',
                'required'          => true
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'JSON value of the parameter (type depends on the setting).'
            ]

        ];
    }
 
}