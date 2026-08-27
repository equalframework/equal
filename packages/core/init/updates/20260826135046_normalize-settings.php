<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;
use core\setting\SettingValue;

$setting_name_map = [
    'core.locale.numbers.thousands_separator'        => 'core.locale.number.thousands_separator',
    'core.locale.numbers.decimal_separator'          => 'core.locale.number.decimal_separator',
    'core.locale.numbers.decimal_precision'          => 'core.locale.number.decimal_precision',
    'core.locale.date_format'                        => 'core.locale.date.format',
    'core.locale.time_format'                        => 'core.locale.time.format',
    'core.locale.currency'                           => 'core.locale.currency.symbol',
    'core.units.currency'                            => 'core.locale.currency.symbol',
    'core.locale.length'                             => 'core.locale.unit.length',
    'core.units.length'                              => 'core.locale.unit.length',
    'core.locale.weight'                             => 'core.locale.unit.weight',
    'core.units.weight'                              => 'core.locale.unit.weight',
    'core.locale.volume'                             => 'core.locale.unit.volume',
    'core.units.volume'                              => 'core.locale.unit.volume',
    'core.locale.surface'                            => 'core.locale.unit.surface',
    'core.units.surface'                             => 'core.locale.unit.surface',
    'core.locale.time_zone'                          => 'core.locale.time.zone',
    'core.locale.time_encoding'                      => 'core.system.time.encoding',
    'core.security.passkey_creation'                 => 'core.security.auth.passkey.creation',
    'core.security.passkey_rp_id'                    => 'core.security.auth.passkey.rp_id',
    'core.security.passkey_rp_name'                  => 'core.security.auth.passkey.rp_name',
    'core.security.passkey_format_android-key'       => 'core.security.auth.passkey.format.android-key',
    'core.security.passkey_format_android-safetynet' => 'core.security.auth.passkey.format.android-safetynet',
    'core.security.passkey_format_apple'             => 'core.security.auth.passkey.format.apple',
    'core.security.passkey_format_fido-u2f'          => 'core.security.auth.passkey.format.fido-u2f',
    'core.security.passkey_format_none'              => 'core.security.auth.passkey.format.none',
    'core.security.passkey_format_packed'            => 'core.security.auth.passkey.format.packed',
    'core.security.passkey_format_tpm'               => 'core.security.auth.passkey.format.tpm',
    'core.security.passkey_user_verification'        => 'core.security.auth.passkey.user_verification',
    'core.security.passkey_cross_platform'           => 'core.security.auth.passkey.cross_platform',
    'core.security.passkey_authenticator_usb'        => 'core.security.auth.passkey.authenticator.usb',
    'core.security.passkey_authenticator_nfc'        => 'core.security.auth.passkey.authenticator.nfc',
    'core.security.passkey_authenticator_ble'        => 'core.security.auth.passkey.authenticator.ble',
    'core.security.passkey_authenticator_hybrid'     => 'core.security.auth.passkey.authenticator.hybrid',
    'core.security.passkey_authenticator_internal'   => 'core.security.auth.passkey.authenticator.internal',
    'core.security.totpkey_creation'                 => 'core.security.auth.totp.creation'
];

$index_settings = static function(): array {
    $result = [];
    $settings = Setting::search([])
        ->read(['package', 'section', 'code'])
        ->get();

    foreach($settings as $setting_id => $setting) {
        $name = implode('.', [
            $setting['package'],
            $setting['section'],
            $setting['code']
        ]);
        $result[$name] = $setting_id;
    }

    return $result;
};

$preexisting_setting_ids = $index_settings();
$deprecated_setting_ids = [];
$pending_value_migrations = [];

foreach($setting_name_map as $old_name => $new_name) {
    if(!isset($preexisting_setting_ids[$old_name])) {
        continue;
    }

    $old_setting_id = $preexisting_setting_ids[$old_name];
    $deprecated_setting_ids[] = $old_setting_id;

    // Preserve canonical values when the normalized setting already existed before this update.
    // For legacy aliases sharing a target, the first existing source in the map takes precedence.
    if(
        isset($preexisting_setting_ids[$new_name])
        || isset($pending_value_migrations[$new_name])
    ) {
        continue;
    }

    $pending_value_migrations[$new_name] = SettingValue::search([
        ['setting_id', '=', $old_setting_id]
    ])->read(['user_id', 'value'])->get();
}

if(count($deprecated_setting_ids)) {
    Setting::ids(array_values(array_unique($deprecated_setting_ids)))
        ->update(['is_deprecated' => true]);
}

eQual::run('do', 'core_init_settings');

$setting_ids = $index_settings();
$normalized_setting_ids = $deprecated_setting_ids;

foreach($pending_value_migrations as $new_name => $source_values) {
    if(!isset($setting_ids[$new_name])) {
        throw new Exception("Missing normalized setting '{$new_name}'.", EQ_ERROR_INVALID_CONFIG);
    }

    $new_setting_id = $setting_ids[$new_name];
    $normalized_setting_ids[] = $new_setting_id;
    $target_value_ids = [];
    $target_values = SettingValue::search([
        ['setting_id', '=', $new_setting_id]
    ])->read(['user_id'])->get();

    foreach($target_values as $target_value_id => $target_value) {
        $user_id = $target_value['user_id'] ?? null;
        $selector = is_null($user_id) ? 'global' : "user:{$user_id}";
        $target_value_ids[$selector] = $target_value_id;
    }

    foreach($source_values as $source_value) {
        $user_id = $source_value['user_id'] ?? null;
        $selector = is_null($user_id) ? 'global' : "user:{$user_id}";
        $values = ['value' => $source_value['value'] ?? null];

        if(isset($target_value_ids[$selector])) {
            SettingValue::id($target_value_ids[$selector])->update($values);
            continue;
        }

        $target_value = SettingValue::create([
            'setting_id' => $new_setting_id,
            'user_id'    => $user_id
        ] + $values)->first();
        $target_value_ids[$selector] = $target_value['id'];
    }
}

$normalized_setting_ids = array_values(array_unique($normalized_setting_ids));
if(count($normalized_setting_ids)) {
    $normalized_settings = Setting::ids($normalized_setting_ids)
        ->read(['code'])
        ->get();

    foreach($normalized_settings as $setting_id => $setting) {
        Setting::id($setting_id)->update([
            'code' => $setting['code'],
            'name' => null
        ]);
    }
}
