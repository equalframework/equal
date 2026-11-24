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
    'params'        => [
        'id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'core\setting\SettingValue',
            'description'       => 'Identifier of the setting value to update.',
            'required'          => true
        ],
        'value' => [
            'type'              => 'string',
            'description'       => 'New value to assign.',
            'required'          => true
        ],
        'lang' => [
            'type'              => 'string',
            'description'       => 'Language in which to update a multilingual setting.',
            'default'           => constant('DEFAULT_LANG')
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

$setting_values = $orm->read(SettingValue::getType(), [$params['id']], ['setting_id']);

if(!isset($setting_values[$params['id']])) {
    throw new Exception('unknown_setting_value', EQ_ERROR_UNKNOWN_OBJECT);
}

$setting_id = $setting_values[$params['id']]['setting_id'];
$settings = $orm->read(Setting::getType(), [$setting_id], ['is_multilang']);

if(!isset($settings[$setting_id])) {
    throw new Exception('unknown_setting', EQ_ERROR_UNKNOWN_OBJECT);
}

$effective_lang = $settings[$setting_id]['is_multilang']
    ? $params['lang']
    : constant('DEFAULT_LANG');

SettingValue::id($params['id'])
    ->update(['value' => $params['value']], $effective_lang);

$context
    ->httpResponse()
    ->status(204)
    ->send();
