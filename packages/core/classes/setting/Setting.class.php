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
                    'input',
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
                'sort'              => 'asc',
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



    /**
     * Retrieve the value of a given setting.
     *
     * @param   $package    Package to which the setting relates to.
     * @param   $section    Specific section within the package.
     * @param   $code       Unique code of the setting within the given package and section.
     * @param   $user_id    (optional) Retrieve the specific value assigned to a given user.
     *
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_value(string $package, string $section, string $code, int $user_id=0) {
        $result = null;

        $providers = \eQual::inject(['orm']);
        $om = $providers['orm'];

        $settings_ids = $om->search('core\setting\Setting', [
            ['package', '=', $package],
            ['section', '=', $section],
            ['code', '=', $code]
        ]);

        if($settings_ids > 0 && count($settings_ids)) {

            $settings = $om->read('core\setting\Setting', $settings_ids, ['type', 'setting_values_ids']);

            if($settings > 0 && count($settings)) {
                // #memo - there should be exactly one setting matching the criterias
                $setting = array_pop($settings);
                $setting_values = $om->read('core\setting\SettingValue', $setting['setting_values_ids'], ['user_id', 'value']);
                if($setting_values > 0) {
                    $value = null;
                    // #memo - by default settings values are sorted on user_id (which can be null), so first value is the default one
                    foreach($setting_values as $setting_value) {
                        if($setting_value['user_id'] == $user_id) {
                            $value = $setting_value['value'];
                            break;
                        }
                    }
                    if(!is_null($value)) {
                        $result = $value;
                        settype($result, $setting['type']);
                    }
                }
            }
        }

        return $result;
    }


    /**
     * Update the value of a given setting.
     *
     * @param   $value      The new value that has to be assigned to the setting.
     * @param   $package    Package to which the setting relates to.
     * @param   $section    Specific section within the package.
     * @param   $code       Unique code of the setting within the given package and section.
     * @param   $user_id    (optional) Target the specific value assigned to a given user.
     *
     * @return  void
     */
    public static function set_value($value, string $package, string $section, string $code, int $user_id=0) {
        $providers = \eQual::inject(['orm']);
        $om = $providers['orm'];

        $settings_ids = $om->search('core\setting\Setting', [
            ['package', '=', $package],
            ['section', '=', $section],
            ['code', '=', $code]
        ]);

        if($settings_ids > 0 && count($settings_ids)) {
            $setting_id = array_pop($settings_ids);
            $settings_values_ids = $om->search('core\setting\SettingValue', [ ['setting_id', '=', $setting_id], ['user_id', '=', $user_id] ]);
            if($settings_values_ids > 0 && count($settings_values_ids)) {
                // update the value
                $om->write('core\setting\SettingValue', $settings_values_ids, ['value' => $value]);
            }
            else {
                // value does not exist yet: create a new value
                $om->create('core\setting\SettingValue', ['setting_id' => $setting_id, 'value' => $value, 'user_id' => $user_id]);
            }
        }
    }



    public static function parse_format($format, $map) {
        preg_match_all('/({.*})/mU', $format, $matches, PREG_OFFSET_CAPTURE, 0);

        $result_format = '';
        $last_offset = 0;

        $parts = [];

        if(count($matches[0])) {
            foreach($matches[0] as $match) {
                $len = strlen($match[0]);
                $offset = $match[1];
                $result_format .= substr($format, $last_offset, ($offset-$last_offset));
                $last_offset = $offset+$len;
                $parts[] = str_replace(['{', '}'], '', $match[0]);
            }
        }

        $values = [];

        foreach($parts as $part) {
            $values[] = $map[$part];
        }

        return vsprintf($result_format, $values);
    }
}