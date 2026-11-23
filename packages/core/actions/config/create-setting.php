<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\setting\SettingSection;

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create (or to reset) a setting given its package, section & code.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package the setting relates to.',
            'required'      => true
        ],
        'section' => [
            'type'          => 'string',
            'description'   => 'Section identifier in which to create the setting.',
            'required'      => true
        ],
        'code' => [
            'type'          => 'string',
            'description'   => 'Unique identifier of the setting.',
            'required'      => true
        ],
        'value' => [
            'type'          => 'string',
            'description'   => 'Value to assign to the setting.',
            'required'      => true
        ],
        'type' => [
            'type'          => 'string',
            'description'   => 'Type of the values associated to the setting.',
            'selection'     => [
                    'boolean',
                    'integer',
                    'float',
                    'string',
                    'many2one'
            ],
            'default'       => 'string'
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$packages = eQual::run('get', 'core_config_packages');

$map_packages = array_flip($packages);

if(!isset($map_packages[$params['package']])) {
    throw new Exception('invalid_package', EQ_ERROR_INVALID_PARAM);
}

$settingSection = SettingSection::search(['code', '=', $params['section']])->first();

if(!$settingSection) {
    throw new Exception('invalid_section', EQ_ERROR_INVALID_PARAM);
}

Setting::assert_value($params['package'], $params['section'], $params['code'], $params['value']);

$context
    ->httpResponse()
    ->status(201)
    ->send();