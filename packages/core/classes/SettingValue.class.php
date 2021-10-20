<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

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
                'function'          => 'core\SettingValue::getDisplayName',
                'store'             => true,
                'readonly'          => true
            ],

            'setting_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\Setting',
                'description'       => 'Setting the value relates to.',
                'required'          => true
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User the setting is specific to (optional).',
                'default'           => 0
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'JSON value of the parameter.'
            ]

        ];
    }

    public static function getDisplayName($om, $oids, $lang) {
        $result = [];

        $settingValues = $om->read(__CLASS__, $oids, ['setting_id.name'], $lang);

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