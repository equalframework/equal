<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class Setting extends Model {

    public static function constants() {
        return ['DEFAULT_LANG'];
    }

    public static function getName() {
        return 'Configuration parameter';
    }

    public static function getDescription() {
        return "Configurations parameters may be used by any package and provide specific information about current installation.";
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Full path setting code to serve as reference (unique).",
                'function'          => 'calcName',
                'store'             => true,
                'readonly'          => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Unique code of the parameter.',
                'required'          => true,
                'dependents'        => ['name', 'setting_values_ids' => ['name']]
            ],

            'package' => [
                'type'              => 'string',
                'description'       => 'Package which the param refers to, if any.',
                'default'           => 'core',
                'dependents'        => ['name', 'setting_values_ids' => ['name']]
            ],

            'section' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Section name the setting belongs to.",
                'function'          => 'calcSection',
                'store'             => true,
                'readonly'          => true
            ],

            'section_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\setting\SettingSection',
                'description'       => 'Section the setting relates to.',
                'required'          => true,
                'dependents'        => ['section', 'name', 'setting_values_ids' => ['name']]
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
                'usage'             => 'text/plain',
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

            'is_multilang' => [
                'type'              => 'boolean',
                'description'       => "Marks the setting as translatable.",
                'default'           => false
            ],

            'is_sequence' => [
                'type'              => 'boolean',
                'description'       => "Marks the setting as a numeric sequence.",
                'default'           => false
            ],

            'form_control' => [
                'type'              => 'string',
                'selection'         => [
                    'select',
                    'toggle',
                    'input',
                    'textarea'
                ],
                'usage'             => 'text/plain',
                'description'       => 'Way in which the form is presented to the User.',
                'multilang'         => true
            ],

            'setting_values_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingValue',
                'foreign_field'     => 'setting_id',
                'sort'              => 'asc',
                'order'             => 'user_id',
                'description'       => 'List of values related to the setting.',
                'visible'           => ['is_sequence', '=', false]
            ],

            'setting_sequences_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingSequence',
                'foreign_field'     => 'setting_id',
                'sort'              => 'asc',
                'order'             => 'user_id',
                'description'       => 'List of sequences related to the setting.',
                'visible'           => ['is_sequence', '=', true]
            ],

            'setting_choices_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingChoice',
                'foreign_field'     => 'setting_id',
                'description'       => 'List of possible values related to the setting.'
            ]

        ];
    }

    public static function calcSection($self) {
        $result = [];
        $self->read(['section_id' => 'code']);
        foreach($self as $id => $setting) {
            $result[$id] = $setting['section_id']['code'];
        }
        return $result;
    }

    public static function calcName($self) {
        $result = [];
        $self->read(['package', 'section', 'code']);
        foreach($self as $id => $setting) {
            $result[$id] = $setting['package'].'.'.$setting['section'].'.'.$setting['code'];
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
     * @param   $default    (optional) Default value to return if setting is not found.
     * @param   $user_id    (optional) Retrieve the specific value assigned to a given user.
     * @param   $lang       (optional) Lang in which to retrieve the value (for multilang settings).
     *
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_value(string $package, string $section, string $code, $default=null, int $user_id=0, string $lang='en') {
        $result = $default;

        // #memo - we use a dedicated cache since several o2m fields are involved and we want to prevent loading the same value multiple times in a same thread
        $index = $package.'.'.$section.'.'.$code.'.'.$user_id.'.'.$lang;
        if(!isset($GLOBALS['_equal_core_setting_cache'])) {
            $GLOBALS['_equal_core_setting_cache'] = [];
        }

        if(isset($GLOBALS['_equal_core_setting_cache'][$index])) {
            return $GLOBALS['_equal_core_setting_cache'][$index];
        }

        $providers = \eQual::inject(['orm']);
        /** @var \equal\orm\ObjectManager */
        $om = $providers['orm'];

        $settings_ids = $om->search(self::getType(), [
            ['package', '=', $package],
            ['section', '=', $section],
            ['code', '=', $code]
        ]);

        if($settings_ids > 0 && count($settings_ids)) {

            $settings = $om->read(self::getType(), $settings_ids, ['type', 'is_multilang', 'setting_values_ids']);

            if($settings > 0 && count($settings)) {
                // #memo - there should be exactly one setting matching the criterias
                $setting = array_pop($settings);

                $values_lang = constant('DEFAULT_LANG');
                if($setting['is_multilang']) {
                    $values_lang = $lang;
                }

                $setting_values = $om->read(SettingValue::getType(), $setting['setting_values_ids'], ['user_id', 'value'], $values_lang);
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

        $GLOBALS['_equal_core_setting_cache'][$index] = $result;
        return $result;
    }


    /**
     * Update the value of a given setting.
     *
     * @param   $package    Package to which the setting relates to.
     * @param   $section    Specific section within the package.
     * @param   $code       Unique code of the setting within the given package and section.
     * @param   $value      The new value that has to be assigned to the setting.
     * @param   $user_id    (optional) Target the specific value assigned to a given user.
     *
     * @return  void
     */
    public static function set_value(string $package, string $section, string $code, $value, int $user_id=0, $lang='en') {
        $providers = \eQual::inject(['orm']);
        $om = $providers['orm'];

        $sections_ids = $om->search(SettingSection::getType(), ['code', '=', $section]);
        if(!count($sections_ids)) {
            // section does not exist yet
            $sections_ids = (array) $om->create(SettingSection::getType(), ['name' => $section, 'code' => $section]);
        }
        $section_id = reset($sections_ids);

        $settings_ids = $om->search(self::getType(), [
            ['package', '=', $package],
            ['section_id', '=', $section_id],
            ['code', '=', $code]
        ]);

        if(!count($settings_ids)) {
            // setting does not exist yet
            $settings_ids = (array) $om->create(Setting::getType(), ['package' => $package, 'section_id' => $section_id, 'code' => $code]);
        }
        $setting_id = reset($settings_ids);

        $settings_values_ids = $om->search(SettingValue::getType(), [ ['setting_id', '=', $setting_id], ['user_id', '=', $user_id] ]);
        if(!count($settings_values_ids)) {
            // value does not exist yet: create a new value
            $om->create(SettingValue::getType(), ['setting_id' => $setting_id, 'value' => $value, 'user_id' => $user_id], $lang);
        }
        else {
            // update existing value
            $om->update(SettingValue::getType(), $settings_values_ids, ['value' => $value], $lang);
        }

        // #memo - we use a dedicated cache since several o2m fields are involved and we want to prevent loading the same value multiple times in a same thread
        $index = $package.'.'.$section.'.'.$code.'.'.$user_id.'.'.$lang;
        if(!isset($GLOBALS['_equal_core_setting_cache'])) {
            $GLOBALS['_equal_core_setting_cache'] = [];
        }
        $GLOBALS['_equal_core_setting_cache'][$index] = $value;
    }

    /**
     * $selector is expected to hold any additional field that can be used to differentiate a SettingValue record (fields can be added in inherited classes)
     */
    public static function fetch_and_add(string $package, string $section, string $code, $increment=null, array $selector=[], string $lang='en') {
        $result = null;

        $providers = \eQual::inject(['orm']);
        /** @var \equal\orm\ObjectManager */
        $orm = $providers['orm'];

        $settings_ids = $orm->search(self::getType(), [
            ['package', '=', $package],
            ['section', '=', $section],
            ['code', '=', $code]
        ]);

        if($settings_ids > 0 && count($settings_ids)) {

            $settings = $orm->read(self::getType(), $settings_ids, ['type', 'setting_sequences_ids']);

            if($settings > 0 && count($settings)) {
                // #memo - there should be exactly one setting matching the criterias
                $setting = array_pop($settings);

                $setting_sequence_id = 0;
                $setting_sequences = $orm->read(SettingSequence::getType(), $setting['setting_sequences_ids'], ['id']);
                if($setting_sequences > 0) {
                    foreach($setting_sequences as $sequence) {
                        $setting_sequence_id = $sequence['id'];
                        break;
                    }
                }
                if($setting_sequence_id > 0) {
                    $result = $orm->fetchAndAdd(SettingSequence::getType(), $setting_sequence_id, 'value', $increment);
                }
            }
        }

        return $result;
    }

    public static function format_number($number, $decimal_precision=null) {
        $thousands_separator = Setting::get_value('core', 'locale', 'numbers.thousands_separator', '.');
        $decimal_separator = Setting::get_value('core', 'locale', 'numbers.decimal_separator', ',');

        if(is_null($decimal_precision)) {
            $decimal_precision = Setting::get_value('core', 'locale', 'numbers.decimal_precision', 2);
        }
        return number_format($number, $decimal_precision, $decimal_separator, $thousands_separator);
    }

    public static function format_number_currency($amount) {
        $result = '';
        $decimal_precision = Setting::get_value('core', 'locale', 'currency.decimal_precision', 2);
        $symbol_position = Setting::get_value('core', 'locale', 'currency.symbol_position', 'before');
        $currency = Setting::get_value('core', 'units', 'currency', '$');

        $result = self::format_number($amount, $decimal_precision);
        if($symbol_position == 'after') {
            $result = $result.' '.$currency;
        }
        else if($symbol_position == 'before') {
            $result = $currency.' '.$result;
        }
        return $result;
    }

    /**
     * Example:
     *  parse_format("start{test}%2d{year}/%02d{org}/%04d{sequence}end", ['test' => 'ok', 'year'=> 2022, 'org' => 998, 'sequence' => 14]);
     */
    public static function parse_format($format, $map) {
        preg_match_all('/((%[0-9]{1,2}[ds])?({.*}))/mU', $format, $matches, PREG_OFFSET_CAPTURE, 0);

        $result_format = '';
        $last_offset = 0;

        $parts = [];
        $formats_map = [];

        if(count($matches[2])) {
            foreach($matches[2] as $match) {
                $formats_map[] = $match[0];
            }
        }

        if(count($matches[3])) {
            foreach($matches[3] as $i => $match) {
                $len = strlen($match[0]);
                $offset = $match[1];

                if($i == 0) {
                    $result_format .= substr($format, 0, $offset);
                    $last_offset = $offset;
                }

                $val_format = $formats_map[$i];
                if(strlen($val_format) <= 0) {
                    $result_format .= '%s';
                }
                $result_format .= substr($format, $last_offset, ($offset-$last_offset));
                $last_offset = $offset+$len;
                $parts[] = str_replace(['{', '}'], '', $match[0]);
            }
        }

        $result_format .= substr($format, $last_offset);

        $values = [];

        foreach($parts as $i => $part) {
            $value = $map[$part];
            $val_format = $formats_map[$i];
            if(strlen($val_format)) {
                $type = substr($val_format, -1);
                if($type == 'd') {
                    $mod = pow(10,  intval(str_replace(['%', 'd'], '', $val_format)));
                    $value = $value % $mod;
                }
            }
            $values[] = $value;
        }

        return vsprintf($result_format, $values);
    }
}
