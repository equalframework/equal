<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\SettingSection;

$create_setting_section = static function(array $definition): void {
    $translations = $definition['translations'] ?? [];
    unset($definition['translations'], $definition['id']);

    $section = SettingSection::create($definition, 'en')->first();

    foreach($translations as $lang => $translation) {
        SettingSection::id($section['id'])->update($translation, $lang);
    }
};

$setting_sections_file = __DIR__ . '/setting-sections.json';
if(!is_file($setting_sections_file)) {
    throw new Exception('missing_setting_sections_definition_file', EQ_ERROR_INVALID_CONFIG);
}

$setting_sections_json = file_get_contents($setting_sections_file);
if($setting_sections_json === false) {
    throw new Exception('missing_setting_sections_definition_file', EQ_ERROR_INVALID_CONFIG);
}

$setting_sections_definitions = json_decode($setting_sections_json, true, 512, JSON_THROW_ON_ERROR);
if(!is_array($setting_sections_definitions) || $setting_sections_definitions !== array_values($setting_sections_definitions)) {
    throw new Exception('invalid_setting_sections_definitions', EQ_ERROR_INVALID_CONFIG);
}

foreach($setting_sections_definitions as $definition) {
    if(!is_array($definition)) {
        throw new Exception('invalid_setting_section_json_object', EQ_ERROR_INVALID_CONFIG);
    }
    $create_setting_section($definition);
}
