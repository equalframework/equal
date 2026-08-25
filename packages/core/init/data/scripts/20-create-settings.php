<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;
use core\setting\SettingChoice;
use core\setting\SettingSection;
use core\setting\SettingSequence;
use core\setting\SettingValue;

$create_setting = static function(array $definition): void {
    static $section_ids = null;

    if($section_ids === null) {
        $section_ids = [];
        $sections = SettingSection::search([])->read(['code'])->get();
        foreach($sections as $section_id => $section) {
            $section_ids[$section['code']] = $section_id;
        }
    }

    $setting_definition = array_diff_key($definition, [
        'translations' => true,
        'choices'      => true,
        'value'        => true,
        'sequences'    => true
    ]);
    $section_code = $setting_definition['section'] ?? null;
    if(!isset($section_ids[$section_code])) {
        throw new Exception("Unknown setting section code '{$section_code}'.");
    }

    unset($setting_definition['section']);
    $setting_definition['section_id'] = $section_ids[$section_code];

    $setting = Setting::create($setting_definition, 'en')->first();
    $setting_id = $setting['id'];

    foreach($definition['translations'] ?? [] as $lang => $translation) {
        Setting::id($setting_id)->update($translation, $lang);
    }

    foreach($definition['choices'] ?? [] as $choice_definition) {
        $translations = $choice_definition['translations'] ?? [];
        unset($choice_definition['translations']);

        $choice = SettingChoice::create([
            'setting_id' => $setting_id
        ] + $choice_definition, 'en')->first();

        foreach($translations as $lang => $translation) {
            SettingChoice::id($choice['id'])->update($translation, $lang);
        }
    }

    // #memo - we do not set lang here to force fallback to DEFAULT_LANG, since these values are not multilang.
    if(array_key_exists('value', $definition)) {
        SettingValue::create([
            'setting_id' => $setting_id,
            'value'      => $definition['value']
        ])->first();
    }

    foreach($definition['sequences'] ?? [] as $sequence) {
        SettingSequence::create([
            'setting_id' => $setting_id
        ] + $sequence)->first();
    }
};

$settings_file = __DIR__ . '/settings.json';
if(!is_file($settings_file)) {
    throw new Exception('missing_settings_definition_file', EQ_ERROR_INVALID_CONFIG);
}

$settings_json = file_get_contents($settings_file);
if($settings_json === false) {
    throw new Exception('missing_settings_definition_file', EQ_ERROR_INVALID_CONFIG);
}

$settings_definitions = json_decode($settings_json, true, 512, JSON_THROW_ON_ERROR);
if(!is_array($settings_definitions) || $settings_definitions !== array_values($settings_definitions)) {
    throw new Exception('invalid_settings_definitions', EQ_ERROR_INVALID_CONFIG);
}

foreach($settings_definitions as $definition) {
    if(!is_array($definition)) {
        throw new Exception('invalid_setting_json_object', EQ_ERROR_INVALID_CONFIG);
    }
    $create_setting($definition);
}
