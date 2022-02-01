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
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Full path setting code to serve as reference (unique).",
                'function'          => 'core\Setting::getDisplayName',
                'store'             => true,
                'readonly'          => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Unique code of the parameter.',
                'onchange'          => 'core\Setting::onchangeCode',
                'required'          => true
            ],

            'package' => [
                'type'              => 'string',
                'description'       => 'Package which the param refers to, if any.',
                'onchange'          => 'core\Setting::onchangePackage',
                'default'           => 'core'
            ],

            'section' => [
                'type'              => 'string',
                'description'       => 'Section to ease param retrieval.',
                'onchange'          => 'core\Setting::onchangeSection',
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

            'help' => [
                'type'              => 'string',
                'usage'             => 'string/text',
                'description'       => 'Detailed description about the setting role.',
                'multilang'         => true
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => ['boolean', 'integer', 'float', 'string', 'selection'],
                'description'       => 'The format of data stored by the param.',
                'required'          => true
            ],

            'form' => [
                'type'              => 'string',
                'usage'             => 'string/text',
                'description'       => 'Way in which the form is presented to the User',
                'multilang'         => true
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

    public static function onchangeCode($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null], $lang);
    }

    public static function onchangeSection($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null], $lang);
    }

    public static function onchangePackage($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null], $lang);
    }

    public static function getDisplayName($om, $oids, $lang) {
        $result = [];

        $settings = $om->read(__CLASS__, $oids, ['package', 'section', 'code'], $lang);

        foreach($settings as $oid => $odata) {
            $result[$oid] = $odata['package'].'.'.$odata['section'].'.'.$odata['code'];
        }

        return $result;
    }

    public function getUnique() {
        return [
            ['package', 'section', 'code']
        ];
    }
}