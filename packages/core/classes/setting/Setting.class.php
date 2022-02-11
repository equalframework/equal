<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

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
                'function'          => 'core\setting\Setting::getDisplayName',
                'store'             => true,
                'readonly'          => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Unique code of the parameter.',
                'onchange'          => 'core\setting\Setting::onchangeCode',
                'required'          => true
            ],

            'package' => [
                'type'              => 'string',
                'description'       => 'Package which the param refers to, if any.',
                'onchange'          => 'core\setting\Setting::onchangePackage',
                'default'           => 'core'
            ],

            'section' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Section name the setting belongs to.",
                'function'          => 'core\setting\Setting::getSection',
                'store'             => true,
                'readonly'          => true
            ],

            'section_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\SettingSection',
                'onchange'          => 'core\setting\Setting::onchangeSectionId',                
                'description'       => 'Section the setting relates to.',
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
                'description'       => "Detailed description about the setting role.",
                'multilang'         => true
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => [
                    'boolean', 
                    'integer', 
                    'float', 
                    'string'
                ],
                'description'       => 'The format of data stored by the param.',
                'required'          => true
            ],

            'form_control' => [
                'type'              => 'string',
                'selection'         => [
                    'select',
                    'toggle',
                    'string',
                    'text'
                ],
                'usage'             => 'string/text',
                'description'       => 'Way in which the form is presented to the User.',
                'multilang'         => true
            ],

            'setting_values_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingValue',
                'foreign_field'     => 'setting_id',
                'sort'              => 'desc',
                'order'             => 'user_id',
                'description'       => 'List of values related to the setting.'
            ],

            'setting_choices_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingChoice',
                'foreign_field'     => 'setting_id',
                'description'       => 'List of values related to the setting.'
            ]

        ];
    }

    public static function onchangeCode($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null], $lang);
    }

    public static function onchangeSectionId($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null, 'section' => null], $lang);
    }

    public static function onchangePackage($om, $ids, $lang) {
        $om->write(__CLASS__, $ids, ['name' => null], $lang);
    }

    public static function getSection($om, $oids, $lang) {
        $result = [];

        $settings = $om->read(__CLASS__, $oids, ['section_id.code'], $lang);

        if($settings > 0 && count($settings)) {
            foreach($settings as $oid => $odata) {
                $result[$oid] = $odata['section_id.code'];
            }    
        }

        return $result;        
    }
    
    public static function getDisplayName($om, $oids, $lang) {
        $result = [];

        $settings = $om->read(__CLASS__, $oids, ['package', 'section', 'code'], $lang);

        if($settings > 0 && count($settings)) {
            foreach($settings as $oid => $odata) {
                $result[$oid] = $odata['package'].'.'.$odata['section'].'.'.$odata['code'];
            }
        }
        
        return $result;
    }

    public function getUnique() {
        return [
            ['package', 'section_id', 'code']
        ];
    }
}