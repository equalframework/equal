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
        return "Configurations parameters are either specific to a user or global (user_id = 0).";
    }

    public static function getColumns() {
        return [

            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Code to serve as reference (might not be unique).",
                'function'          => 'calcName',
                'store'             => true,
                'readonly'          => true
            ],

            'setting_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\Setting',
                'description'       => 'Setting the value relates to.',
                'required'          => true
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User the setting is specific to (optional).',
                'default'           => 0
            ],

            // #memo - for settings not having the is_multilang field set, translations are ignored
            'value' => [
                'type'              => 'string',
                'description'       => 'JSON value of the parameter.',
                'multilang'         => true
            ]

        ];
    }

    public static function calcName($om, $oids, $lang) {
        $result = [];

        $settingValues = $om->read(self::getType(), $oids, ['setting_id.name'], $lang);

        foreach($settingValues as $oid => $odata) {
            $result[$oid] = $odata['setting_id.name'];
        }

        return $result;
    }

    public function getUnique() {
        return [
            ['setting_id', 'user_id']
        ];
    }
}
