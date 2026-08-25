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

[$params, $providers] = eQual::announce([
    'description' => 'Create core setting sections, settings, translations, and choices that are missing from the current installation.',
    'params'      => [],
    'response'    => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'      => [
        'visibility' => 'protected',
        'groups'     => ['admins']
    ],
    'providers'   => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$settings_file = EQ_BASEDIR . '/packages/core/init/data/scripts/settings.json';
if(!is_file($settings_file)) {
    throw new Exception('settings_definition_file_missing', EQ_ERROR_INVALID_CONFIG);
}

$settings_json = file_get_contents($settings_file);
if($settings_json === false) {
    throw new Exception('settings_definition_file_unreadable', EQ_ERROR_INVALID_CONFIG);
}

$settings_definitions = json_decode($settings_json, true);
if(
    !is_array($settings_definitions)
    || $settings_definitions !== array_values($settings_definitions)
    || json_last_error() !== JSON_ERROR_NONE
) {
    throw new Exception('settings_definition_file_invalid', EQ_ERROR_INVALID_CONFIG);
}

$setting_sections_file = EQ_BASEDIR . '/packages/core/init/data/scripts/setting-sections.json';
if(!is_file($setting_sections_file)) {
    throw new Exception('setting_sections_definition_file_missing', EQ_ERROR_INVALID_CONFIG);
}

$setting_sections_json = file_get_contents($setting_sections_file);
if($setting_sections_json === false) {
    throw new Exception('setting_sections_definition_file_unreadable', EQ_ERROR_INVALID_CONFIG);
}

$setting_sections_definitions = json_decode($setting_sections_json, true);
if(
    !is_array($setting_sections_definitions)
    || $setting_sections_definitions !== array_values($setting_sections_definitions)
    || json_last_error() !== JSON_ERROR_NONE
) {
    throw new Exception('setting_sections_definition_file_invalid', EQ_ERROR_INVALID_CONFIG);
}

foreach($setting_sections_definitions as $definition) {
    if(
        !is_array($definition)
        || !isset($definition['code'])
        || !is_string($definition['code'])
        || $definition['code'] === ''
        || (isset($definition['translations']) && !is_array($definition['translations']))
    ) {
        throw new Exception('setting_section_definition_invalid', EQ_ERROR_INVALID_CONFIG);
    }
}

$section_ids = [];
$sections = SettingSection::search([])->read(['code'])->get();
foreach($sections as $section_id => $section) {
    $section_ids[$section['code']] = $section_id;
}

$created_sections = [];
foreach($setting_sections_definitions as $definition) {
    $section_code = $definition['code'];
    if(isset($section_ids[$section_code])) {
        continue;
    }

    $translations = $definition['translations'] ?? [];
    unset($definition['translations']);

    $section = SettingSection::create($definition, 'en')->first();
    $section_id = $section['id'];

    foreach($translations as $lang => $translation) {
        SettingSection::id($section_id)->update($translation, $lang);
    }

    $section_ids[$section_code] = $section_id;
    $created_sections[] = $section_code;
}

$existing_setting_keys = [];
$settings = Setting::search([])->read(['package', 'section_id', 'code', 'is_deprecated'])->get();
foreach($settings as $setting_id => $setting) {
    $key = implode('.', [$setting['package'], $setting['section_id'], $setting['code']]);
    $existing_setting_keys[$key] = $setting_id;
}

$create_setting = static function(array $definition, int $section_id): int {
    $setting_definition = array_diff_key($definition, [
        'translations' => true,
        'choices'      => true,
        'value'        => true,
        'sequences'    => true
    ]);
    unset($setting_definition['section']);
    $setting_definition['section_id'] = $section_id;

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

    return $setting_id;
};

$sync_missing_translation = static function(string $entity, int $id, string $lang, array $translation): bool {
    if(!count($translation)) {
        return false;
    }

    $current_translation = $entity::id($id)
        ->read(array_keys($translation), $lang)
        ->first();

    $missing_fields = [];
    foreach($translation as $field => $value) {
        if(!isset($current_translation[$field])) {
            $missing_fields[$field] = $value;
        }
    }

    if(!count($missing_fields)) {
        return false;
    }

    $entity::id($id)->update($missing_fields, $lang);
    return true;
};

$sync_missing_translations = static function(string $entity, int $id, array $default_translation, array $translations) use ($sync_missing_translation): bool {
    $updated = $sync_missing_translation($entity, $id, 'en', $default_translation);

    foreach($translations as $lang => $translation) {
        $updated = $sync_missing_translation($entity, $id, $lang, $translation) || $updated;
    }

    return $updated;
};

$sync_missing_choices = static function(int $setting_id, array $choice_definitions) use ($sync_missing_translations): bool {
    $choice_ids = [];
    $choices = SettingChoice::search([
        ['setting_id', '=', $setting_id]
    ])->read(['value'])->get();

    foreach($choices as $choice_id => $choice) {
        if(!array_key_exists($choice['value'], $choice_ids)) {
            $choice_ids[$choice['value']] = $choice_id;
        }
    }

    $updated = false;
    foreach($choice_definitions as $choice_definition) {
        $translations = $choice_definition['translations'] ?? [];
        unset($choice_definition['translations']);

        $choice_value = $choice_definition['value'];
        if(!array_key_exists($choice_value, $choice_ids)) {
            $choice = SettingChoice::create([
                'setting_id' => $setting_id
            ] + $choice_definition, 'en')->first();
            $choice_id = $choice['id'];
            $choice_ids[$choice_value] = $choice_id;
            $updated = true;
        }
        else {
            $choice_id = $choice_ids[$choice_value];
        }

        $updated = $sync_missing_translations(
            SettingChoice::class,
            $choice_id,
            array_intersect_key($choice_definition, ['name' => true]),
            $translations
        ) || $updated;
    }

    return $updated;
};

$created = [];
$existing = [];
$ignored_deprecated = [];

foreach($settings_definitions as $definition) {
    if(
        !is_array($definition)
        || !isset($definition['package'], $definition['section'], $definition['code'])
        || !is_string($definition['package'])
        || !is_string($definition['section'])
        || !is_string($definition['code'])
        || $definition['package'] === ''
        || $definition['section'] === ''
        || $definition['code'] === ''
    ) {
        throw new Exception('settings_definition_invalid', EQ_ERROR_INVALID_CONFIG);
    }

    $setting_name = implode('.', [
        $definition['package'],
        $definition['section'],
        $definition['code']
    ]);

    $section_code = $definition['section'];
    if(!isset($section_ids[$section_code])) {
        if(($definition['is_deprecated'] ?? false) === true) {
            $ignored_deprecated[] = $setting_name;
            continue;
        }
        throw new Exception('setting_section_unknown', EQ_ERROR_INVALID_CONFIG);
    }

    $setting_key = implode('.', [
        $definition['package'],
        $section_ids[$section_code],
        $definition['code']
    ]);

    if(($definition['is_deprecated'] ?? false) === true) {
        if(isset($existing_setting_keys[$setting_key])) {
            $setting_id = $existing_setting_keys[$setting_key];
            if(!(bool) ($settings[$setting_id]['is_deprecated'] ?? false)) {
                Setting::id($setting_id)->update(['is_deprecated' => true]);
            }
        }

        $ignored_deprecated[] = $setting_name;
        continue;
    }

    if(isset($existing_setting_keys[$setting_key])) {
        $setting_id = $existing_setting_keys[$setting_key];
        $sync_missing_translations(
            Setting::class,
            $setting_id,
            array_intersect_key($definition, [
                'title'       => true,
                'description' => true,
                'help'        => true
            ]),
            $definition['translations'] ?? []
        );
        $sync_missing_choices($setting_id, $definition['choices'] ?? []);

        $existing[] = $setting_name;
        continue;
    }

    $setting_id = $create_setting($definition, $section_ids[$section_code]);
    $existing_setting_keys[$setting_key] = $setting_id;
    $created[] = $setting_name;
}

$context
    ->httpResponse()
    ->body([
        'created_sections'   => $created_sections,
        'created'            => $created,
        'existing'           => $existing,
        'ignored_deprecated' => $ignored_deprecated
    ])
    ->send();
