<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\setting\SettingValue;

[$params, $providers] = eQual::announce([
    'description'   => 'Updates a setting value in the language supported by its setting.',
    'extends'       => 'core_model_update',
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Entity targeted by a generic form submission.',
            'default'       => 'core\setting\SettingValue'
        ],
        'value' => [
            'type'          => 'string',
            'description'   => 'New value supplied by the dedicated settings app.'
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

if($params['entity'] !== SettingValue::class) {
    throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
}

$ids = array_values(array_unique(array_filter(
    array_map('intval', $params['ids']),
    fn($id) => $id > 0
)));
if(empty($ids)) {
    if($params['id'] <= 0) {
        throw new Exception('object_invalid_id', EQ_ERROR_INVALID_PARAM);
    }
    $ids = [$params['id']];
}

$fields = $params['fields'];
if(array_key_exists('value', $params)) {
    $fields['value'] = $params['value'];
}

$setting_values = $orm->read(SettingValue::getType(), $ids, ['setting_id']);

if(count($setting_values) !== count($ids)) {
    throw new Exception('unknown_setting_value', EQ_ERROR_UNKNOWN_OBJECT);
}

$setting_ids = [];
foreach($setting_values as $setting_value) {
    $setting_ids[] = !empty($fields['setting_id']) && is_numeric($fields['setting_id'])
        ? (int) $fields['setting_id']
        : $setting_value['setting_id'];
}
$setting_ids = array_values(array_unique($setting_ids));
$settings = $orm->read(Setting::getType(), $setting_ids, ['is_multilang']);

if(count($settings) !== count($setting_ids)) {
    throw new Exception('unknown_setting', EQ_ERROR_UNKNOWN_OBJECT);
}

$ids_by_lang = [];
foreach($setting_values as $id => $setting_value) {
    $setting_id = !empty($fields['setting_id']) && is_numeric($fields['setting_id'])
        ? (int) $fields['setting_id']
        : $setting_value['setting_id'];
    $effective_lang = $settings[$setting_id]['is_multilang']
        ? $params['lang']
        : constant('DEFAULT_LANG');
    $ids_by_lang[$effective_lang][] = $id;
}

$result = [];
foreach($ids_by_lang as $effective_lang => $lang_ids) {
    $update_params = [
        'entity'    => SettingValue::class,
        'ids'       => $lang_ids,
        'fields'    => $fields,
        'force'     => $params['force'],
        'lang'      => $effective_lang
    ];

    if(count($ids) === 1) {
        $update_params['previous_state'] = $params['previous_state'];
        $update_params['previous_modified'] = $params['previous_modified'];
    }

    $update_result = eQual::run('do', 'core_model_update', $update_params);
    if(is_array($update_result)) {
        $result = array_merge($result, $update_result);
    }
}

$context
    ->httpResponse()
    ->body($result)
    ->status(200)
    ->send();
