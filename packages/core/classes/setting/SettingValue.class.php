<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class SettingValue extends Model {

    public static function getName() {
        return 'Configuration value';
    }

    public static function getDescription() {
        return "Configuration parameters are either specific to a user or global (user_id = 0).";
    }

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Code to serve as reference (might not be unique).",
                'relation'          => ['setting_id' => 'name'],
                'store'             => true,
                'readonly'          => true
            ],

            'setting_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\Setting',
                'description'       => 'Setting the value relates to.',
                'dependents'        => ['name'],
                'ondelete'          => 'cascade',
                'required'          => true
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User the setting is specific to (optional).',
                'default'           => 0,
                'ondelete'          => 'cascade'
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'JSON value of the parameter.',
                'help'              => 'For settings not having the is_multilang field set, translations are ignored.',
                'multilang'         => true
            ]

        ];
    }

    public function getUnique() {
        return [
            ['setting_id', 'user_id']
        ];
    }
}
