<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class SettingSequence extends Model {

    public static function getName() {
        return 'Configuration value';
    }

    public static function getDescription() {
        return "Configurations parameters are either specific to a user or global (user_id = 0).";
    }

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Code to serve as reference (might not be unique).",
                'relation'          => ['setting_id' => 'name'],
                'store'             => true,
                'readonly'          => true,
                'instant'           => true
            ],

            'setting_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\Setting',
                'description'       => 'Setting the sequence relates to.',
                'ondelete'          => 'cascade',
                'required'          => true,
                'dependents'        => ['name']
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User the setting is specific to (optional).',
                'ondelete'          => 'cascade'
            ],

            'value' => [
                'type'              => 'integer',
                'description'       => 'Value of the sequence.',
                'help'              => 'A sequence is always a positive integer. Sequences can benefit from the fetch_and_add method.',
                'multilang'         => false
            ]

        ];
    }

    public function getUnique() {
        return [
            ['setting_id']
        ];
    }
}
