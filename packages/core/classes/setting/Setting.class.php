<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class Setting extends Model {

    /*
        #memo - this class uses a dedicated cache defined in global scope.
        This is preferred since several o2m fields are involved and we want to avoid loading the same value multiple times in a same thread.
    */

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
                'instant'           => true,
                'readonly'          => true
            ],

            'code' => [
                'type'              => 'string',
                'usage'             => 'text/plain:128',
                'description'       => 'Unique code of the parameter.',
                'required'          => true,
                'dependents'        => ['name', 'setting_values_ids' => ['name']]
            ],

            'package' => [
                'type'              => 'string',
                'usage'             => 'text/plain:128',
                'description'       => 'Package which the param refers to, if any.',
                'default'           => 'core',
                'dependents'        => ['name', 'setting_values_ids' => ['name']]
            ],

            'section' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Section name the setting belongs to.",
                'relation'          => ['section_id' => 'code'],
                'store'             => true,
                'readonly'          => true,
                'instant'           => true
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

            'is_sequence' => [
                'type'              => 'boolean',
                'description'       => "Marks the setting as a numeric sequence.",
                'help'              => "Some settings must have a numeric value, meant to be incremented, and that must match a numeric SQL field in the related table. For tht reason, we use a distinct entity `SettingSequence` for which the `value` field/column is an integer.",
                'default'           => false
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => [
                    'boolean',
                    'integer',
                    'float',
                    'string',
                    'many2one'
                ],
                'description'       => 'The format of data stored by the param.',
                'default'           => 'string',
                'visible'           => ['is_sequence', '=', false]
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => "Full name of the entity the Setting refers to.",
                'visible'           => ['type', '=', 'many2one']
            ],

            'is_multilang' => [
                'type'              => 'boolean',
                'description'       => "Marks the setting as translatable.",
                'default'           => false,
                'visible'           => ['is_sequence', '=', false]
            ],

            'form_control' => [
                'type'              => 'string',
                'selection'         => [
                    'select',
                    'toggle',
                    'input',
                    'textarea'
                ],
                'description'       => 'Way in which the form is presented to the User.',
                'default'           => 'input',
                'visible'           => ['is_sequence', '=', false]
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
                'description'       => 'List of possible values related to the setting.',
                'visible'           => ['is_sequence', '=', false]
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'Value to use as default for subsequent value.',
                'help'              => '`value` is a virtual field meant to be used for creating a default value or sequence associated with a newly created setting.',
                'onupdate'          => 'onupdateValue'
            ]

        ];
    }

    protected static function getSettingValueClass(): string {
        return SettingValue::class;
    }

    protected static function getSettingSequenceClass(): string {
        return SettingSequence::class;
    }

    protected static function getSelectorKeys() {
        return ['user_id'];
    }

    public static function calcName($self) {
        $result = [];
        $self->read(['state', 'package', 'section', 'code']);
        foreach($self as $id => $setting) {
            if($setting['state'] != 'draft') {
                $result[$id] = $setting['package'] . '.' . $setting['section'] . '.' . $setting['code'];
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
     * After creation, if `value` is set, make sure either a value or sequence is set (depending on is_sequence).
     * `value` field acts as a virtual field and used only to simplify initialization through init/data.
     *
     * The value is not expected to change over time — dynamic assignments should be handled via SettingValue or SettingSequence.
     * And this field should not be updated manually outside of the initialization process.
     */
    public static function onupdateValue($self) {
        $self->read(['is_sequence', 'package', 'section', 'code', 'value']);
        foreach($self as $id => $setting) {
            if(!isset($setting['is_sequence'], $setting['package'], $setting['section'], $setting['code'], $setting['value'])) {
                continue;
            }
            if($setting['is_sequence']) {
                static::assert_sequence($setting['package'], $setting['section'], $setting['code'], $setting['value']);
            }
            else {
                static::assert_value($setting['package'], $setting['section'], $setting['code'], $setting['value']);
            }
        }
    }

    /**
     * $index is built this way
     * and cached value is stored in  $GLOBALS['_equal_core_setting_cache'][$index]
     */
    private static function build_cache_index(string $package, string $section, string $code, array $selector = [], ?string $lang = null): string {
        $parts = [$package, $section, $code];

        foreach(static::getSelectorKeys() as $key) {
            $parts[] = $selector[$key] ?? '';
        }

        $parts[] = $lang ?? constant('DEFAULT_LANG');

        return implode('.', $parts);
    }

    private static function get_cache(string $package, string $section, string $code, array $selector = [], ?string $lang = null) {
        $index = static::build_cache_index($package, $section, $code, $selector, $lang);
        return $GLOBALS['_equal_core_setting_cache'][$index] ?? null;
    }

    private static function set_cache(string $package, string $section, string $code, $value, array $selector = [], ?string $lang = null): void {
        $index = static::build_cache_index($package, $section, $code, $selector, $lang);

        if(!isset($GLOBALS['_equal_core_setting_cache'])) {
            $GLOBALS['_equal_core_setting_cache'] = [];
        }

        $GLOBALS['_equal_core_setting_cache'][$index] = $value;
    }

    /**
     * We use $GLOBALS['_equal_core_setting_cache'][$index] with $index {package}.{section}.{code} (no selector) to store the id ofg the matching setting, if any.
     *
     * @return int|null Upon success, method returns the identifier of the matching Setting object as an integer. If the setting does not exist, it returns null.
     */
    private static function get_setting_id($package, $section, $code) {
        // use dedicated cache
        $index = $package.'.'.$section.'.'.$code;

        if(!isset($GLOBALS['_equal_core_setting_cache'][$index])) {

            /** @var \equal\orm\ObjectManager $orm */
            ['orm' => $orm] = \eQual::inject(['orm']);

            // attempt to retrieve the setting
            $settings_ids = $orm->search(static::getType(), [
                    ['package', '=', $package],
                    ['section', '=', $section],
                    ['code', '=', $code]
                ]);

            if(!is_array($settings_ids) || !count($settings_ids)) {
                return null;
            }

            $setting_id = current($settings_ids);

            if(!isset($GLOBALS['_equal_core_setting_cache'])) {
                $GLOBALS['_equal_core_setting_cache'] = [];
            }

            $GLOBALS['_equal_core_setting_cache'][$index] = $setting_id;
        }

        return $GLOBALS['_equal_core_setting_cache'][$index];
    }

    /**
     * Returns a boolean telling of the setting targeted by package, section & code exists or not.
     */
    private static function setting_exists($package, $section, $code) {
        return static::get_setting_id($package, $section, $code) !== null;
    }

    /**
     * @return int|null Upon success, method returns the identifier of the matching SettingValue object as an integer. If the SettingValue does not exist (or if there is ambiguity), it returns null.
     */
    private static function get_setting_value_id($setting_id, $selector) {

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        // create domain based on given setting_id and selector
        $domain = [ ['setting_id', '=', $setting_id] ];

        foreach($selector as $field => $val) {
            $domain[] = [$field, '=', $val];
        }

        // complete domain with missing keys, if any
        $selector_keys = static::getSelectorKeys();
        foreach ($selector_keys as $field) {
            if(!array_key_exists($field, $selector)) {
                $domain[] = [$field, 'is', null];
            }
        }

        // search for candidates values
        $setting_values_ids = $orm->search(static::getSettingValueClass(), $domain);

        if(!is_array($setting_values_ids) || !count($setting_values_ids)) {
            return null;
        }

        // return id of single match, or null in case of ambiguity
        return count($setting_values_ids) === 1 ? current($setting_values_ids) : null;
    }

    /**
     * @return int|null Upon success, method returns the identifier of the matching SettingValue object as an integer. If the SettingValue does not exist (or if there is ambiguity), it returns null.
     */
    private static function get_setting_sequence_id($setting_id, $selector) {

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        // create domain based on given setting_id and selector
        $domain = [ ['setting_id', '=', $setting_id] ];

        foreach($selector as $field => $val) {
            $domain[] = [$field, '=', $val];
        }

        // complete domain with missing keys, if any
        $selector_keys = static::getSelectorKeys();
        foreach ($selector_keys as $field) {
            if(!array_key_exists($field, $selector)) {
                $domain[] = [$field, 'is', null];
            }
        }

        // search for candidates values
        $setting_sequences_ids = $orm->search(static::getSettingSequenceClass(), $domain);

        if(!is_array($setting_sequences_ids) || !count($setting_sequences_ids)) {
            return null;
        }

        // return id of single match, or null in case of ambiguity
        return count($setting_sequences_ids) === 1 ? current($setting_sequences_ids) : null;
    }

    /**
     * Create a setting based on given package, section and code.
     * If setting already exists, creation is ignored and id of existing setting is returned.
     * In case the given sequence does not exist yet, it is also created .
     *
     * Note: creating on-demand sections should be avoided.
     *
     * @return integer  Returns the identifier of the new Section object as an integer.
     */
    private static function create_setting($package, $section, $code, $type = 'string'): int {

        $setting_id = static::get_setting_id($package, $section, $code);

        if($setting_id) {
            return $setting_id;
        }

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        // retrieve section
        $sections_ids = $orm->search(SettingSection::getType(), ['code', '=', $section]);
        if(!count($sections_ids)) {
            trigger_error("APP::creating non-existing setting section {$section}.", EQ_REPORT_INFO);
            // section does not exist yet
            $sections_ids = (array) $orm->create(SettingSection::getType(), ['name' => $section, 'code' => $section]);
        }

        $section_id = current($sections_ids);

        $setting_id = $orm->create(static::getType(), [
                'package'       => $package,
                'section'       => $section,
                'section_id'    => $section_id,
                'code'          => $code,
                'type'          => $type
            ]);

        $index = $package . '.' . $section . '.' . $code;
        $GLOBALS['_equal_core_setting_cache'][$index] = $setting_id;

        return $setting_id;
    }

    /**
     * Create a SettingValue if none match the selector for the given setting_id, and leave its value to null.
     * @return int Returns the id of the existing or newly created SettingValue.
     */
    private static function create_value($setting_id, array $selector=[], $default=null): int {
        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $setting_value_id = static::get_setting_value_id($setting_id, $selector);

        if($setting_value_id) {
            return $setting_value_id;
        }

        $values = [
                'setting_id' => $setting_id,
                'value'      => $default
            ];

        foreach($selector as $field => $val) {
            $values[$field] = $val;
        }

        // complete with missing keys (assigned to null), if any
        $selector_keys = static::getSelectorKeys();
        foreach($selector_keys as $field) {
            if(!array_key_exists($field, $selector)) {
                $values[$field] = null;
            }
        }

        $setting_value_id = $orm->create(static::getSettingValueClass(), $values);

        return $setting_value_id;
    }


    /**
     * Create a SettingSequence if none match the selector for the given setting_id, and leave its value to null.
     * @return int Returns the id of the existing or newly created SettingSequence.
     */
    private static function create_sequence($setting_id, array $selector=[], $default=1): int {
        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $setting_sequence_id = static::get_setting_sequence_id($setting_id, $selector);

        if($setting_sequence_id) {
            return $setting_sequence_id;
        }

        $values = [
                'setting_id'    => $setting_id,
                'value'         => $default
            ];

        foreach($selector as $field => $val) {
            $values[$field] = $val;
        }

        // complete with missing keys (assigned to null), if any
        $selector_keys = static::getSelectorKeys();
        foreach($selector_keys as $field) {
            if(!array_key_exists($field, $selector)) {
                $values[$field] = null;
            }
        }

        $setting_sequence_id = $orm->create(static::getSettingSequenceClass(), $values);

        return $setting_sequence_id;
    }


    /**
     * Sets the value of a given SettingSequence: create it if necessary, and sets it to the given value.
     * If the targeted Setting does not exist, the call fails.
     * If an error occur and the value cannot be set, false is returned. Upon success the newly assigned value is returned.
     *
     * @return  void
     */
    public static function set_sequence(string $package, string $section, string $code, int $value, array $selector=[]) {
        if(!static::setting_exists($package, $section, $code)) {
            return false;
        }

        $setting_id = static::get_setting_id($package, $section, $code);

        if(!$setting_id) {
            return false;
        }

        $setting_sequence_id = static::get_setting_sequence_id($setting_id, $selector);

        if(!$setting_sequence_id) {
            $setting_sequence_id = static::create_sequence($setting_id, $selector);
            if($setting_sequence_id <= 0) {
                return false;
            }
        }

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        // update all targeted values for given lang
        $orm->update(static::getSettingSequenceClass(), (array) $setting_sequence_id, ['value' => $value]);

        return $value;
    }

    /**
     * Make sure the setting exists, create it if necessary.
     * If a new value is created, its value is set to $default.
     * This method is idempotent.
     * @return  void
     */
    public static function assert_value(string $package, string $section, string $code, $default=null, array $selector=[], string $lang=null) {

        if(!static::setting_exists($package, $section, $code)) {
            $type = str_replace('double', 'float', gettype($default));
            $setting_id = static::create_setting(
                $package,
                $section,
                $code,
                in_array($type, ['boolean', 'integer', 'float', 'string'], true) ? $type : 'string'
            );
        }
        else {
            $setting_id = static::get_setting_id($package, $section, $code);
        }

        $setting_value_id = static::get_setting_value_id($setting_id, $selector);

        if(!$setting_value_id) {
            $setting_value_id = static::create_value($setting_id, $selector, $default);
        }
    }

    /**
     * Make sure the setting exists, and create it if necessary.
     * If a new sequence is created, its value is set to $default.
     * This method is idempotent.
     * @return  void
     */
    public static function assert_sequence(string $package, string $section, string $code, $default=1, array $selector=[], string $lang=null) {

        if(!static::setting_exists($package, $section, $code)) {
            $setting_id = static::create_setting($package, $section, $code);
            static::id($setting_id)->update(['is_sequence' => true]);
        }
        else {
            $setting_id = static::get_setting_id($package, $section, $code);
        }

        $setting_sequence_id = static::get_setting_sequence_id($setting_id, $selector);

        if(!$setting_sequence_id) {
            static::create_sequence($setting_id, $selector, $default);
        }
    }

    /**
     * Retrieve the value of a given setting.
     *
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_value(string $package, string $section, string $code, $default=null, array $selector=[], string $lang=null) {

        $lang = $lang ?? constant('DEFAULT_LANG');

        $cached_value = static::get_cache($package, $section, $code, $selector, $lang);

        if(!$cached_value) {

            /** @var \equal\orm\ObjectManager $orm */
            ['orm' => $orm] = \eQual::inject(['orm']);

            $setting_id = static::get_setting_id($package, $section, $code);

            if(!$setting_id) {
                return $default;
            }

            $setting_value_id = static::get_setting_value_id($setting_id, $selector);

            if(!$setting_value_id) {
                return $default;
            }

            $settings = $orm->read(static::getType(), (array) $setting_id, ['type', 'is_multilang']);
            $setting = array_pop($settings);

            $values_lang = ($setting['is_multilang']) ? $lang : constant('DEFAULT_LANG');

            $setting_values = $orm->read(static::getSettingValueClass(), (array) $setting_value_id, ['value'], $values_lang);

            if($setting_values > 0 && count($setting_values)) {

                $value = $setting_values[$setting_value_id]['value'];

                if(!is_null($value)) {
                    $result = $value;

                    $map_types = [
                        'boolean'   => 'boolean',
                        'integer'   => 'integer',
                        'float'     => 'double',
                        'string'    => 'string',
                        'many2one'  => 'integer'
                    ];

                    settype($result, $map_types[$setting['type']] ?? 'string');

                    static::set_cache($package, $section, $code, $result, $selector, $lang);
                }
            }
        }
        return static::get_cache($package, $section, $code, $selector, $lang) ?? $default;
    }

    /**
     * Retrieve the value of a given setting.
     *
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_sequence(string $package, string $section, string $code, $default=null, array $selector=[]) {
        $result = $default;

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $setting_id = static::get_setting_id($package, $section, $code);

        if(!$setting_id) {
            return $default;
        }

        $setting_sequence_id = static::get_setting_sequence_id($setting_id, $selector);

        if(!$setting_sequence_id) {
            return $default;
        }

        $setting_sequences = $orm->read(static::getSettingSequenceClass(), (array) $setting_sequence_id, ['value']);

        if($setting_sequences > 0 && count($setting_sequences)) {
            $result = $setting_sequences[$setting_sequence_id]['value'];
        }

        return $result;
    }

    /**
     * Sets the value of a given setting value: create it if necessary, and sets it to the given value.
     * If the targeted Setting does not exist, the call fails.
     * If an error occur and the value cannot be set, false is returned. Upon success the newly assigned value is returned.
     *
     * @param string        $package    Package to which the setting relates to.
     * @param string        $section    Specific section within the package.
     * @param string        $code       Unique code of the setting within the given package and section.
     * @param mixed         $value      The new value that has to be assigned to the setting.
     * @param array         $selector   (optional) Map used as filter to target a specific value (ex. `[user_id => 2]`).
     * @param string|null   $lang       (optional) Lang in which to retrieve the value (for multilang settings).
     *
     * @return  void
     */
    public static function set_value(string $package, string $section, string $code, $value, array $selector=[], string $lang=null) {
        if(!static::setting_exists($package, $section, $code)) {
            return false;
        }

        $setting_id = static::get_setting_id($package, $section, $code);

        if(!$setting_id) {
            return false;
        }

        $setting_value_id = static::get_setting_value_id($setting_id, $selector);

        if(!$setting_value_id) {
            $setting_value_id = static::create_value($setting_id, $selector);
            if($setting_value_id <= 0) {
                return false;
            }
        }

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $lang = $lang ?? constant('DEFAULT_LANG');

        // update all targeted values for given lang
        $orm->update(static::getSettingValueClass(), (array) $setting_value_id, ['value' => $value], $lang);

        static::set_cache($package, $section, $code, $value, $selector, $lang);

        return $value;
    }

    /**
     * $selector is expected to hold any additional field that can be used to differentiate a SettingValue record (fields can be added in inherited classes)
     */
    public static function fetch_and_add(string $package, string $section, string $code, $increment=1, array $selector=[]) {

        $setting_id = static::get_setting_id($package, $section, $code);

        if(!$setting_id) {
            return null;
        }

        $setting_sequence_id = static::get_setting_sequence_id($setting_id, $selector);

        if(!$setting_sequence_id) {
            return;
        }

        /** @var \equal\orm\ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $res = $orm->fetchAndAdd(static::getSettingSequenceClass(), $setting_sequence_id, 'value', $increment);

        if($res <= 0 || !count($res)) {
            return null;
        }

        // no cache here
        return intval($res[$setting_sequence_id]);
    }



    public static function format_number($number, $decimal_precision=null) {
        $thousands_separator = static::get_value('core', 'locale', 'numbers.thousands_separator', '.');
        $decimal_separator = static::get_value('core', 'locale', 'numbers.decimal_separator', ',');

        if(is_null($decimal_precision)) {
            $decimal_precision = static::get_value('core', 'locale', 'numbers.decimal_precision', 2);
        }
        return number_format($number, $decimal_precision, $decimal_separator, $thousands_separator);
    }

    public static function format_number_currency($amount) {
        $result = '';
        $decimal_precision = static::get_value('core', 'locale', 'currency.decimal_precision', 2);
        $symbol_position = static::get_value('core', 'locale', 'currency.symbol_position', 'before');
        $currency = static::get_value('core', 'locale', 'currency', '$');

        $result = static::format_number($amount, $decimal_precision);
        if($symbol_position == 'after') {
            $result = $result.' '.$currency;
        }
        else if($symbol_position == 'before') {
            $result = $currency.' '.$result;
        }
        return $result;
    }

    /**
     * Examples:
     *  parse_format("start{test}%2d{year}/%02d{org}/%04d{sequence}end", ['test' => 'ok', 'year'=> 2022, 'org' => 998, 'sequence' => 14]);
     *  parse_format("{test1}{test2}%s{test3}", ['test1' => 'ok', 'test2'=> 'blah', 'test3' => 'yes']);
     */
    public static function parse_format($format, $map) {
        preg_match_all('/((%[0-9]{0,2}[ds])?({.*}))/mU', $format, $matches, PREG_OFFSET_CAPTURE, 0);

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

                $val_format = $formats_map[$i] ?? '';
                if(strlen($val_format) <= 0) {
                    $result_format .= '%s';
                }
                $result_format .= substr($format, $last_offset, ($offset-$last_offset));
                $last_offset = $offset + $len;
                $parts[] = str_replace(['{', '}'], '', $match[0]);
            }
        }

        $result_format .= substr($format, $last_offset);

        $values = [];

        foreach($parts as $i => $part) {
            if(!isset($map[$part])) {
                $values[] = null;
                continue;
            }
            $value = $map[$part];
            $val_format = $formats_map[$i] ?? '';
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
