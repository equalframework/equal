<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;
use core\setting\SettingSection;

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create (or to reset) a setting given its package, section & code.",
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package the setting relates to.',
            'type'          => 'string',
            'required'      => true
        ],
        'section' =>  [
            'description'   => 'Section identifier in which to create the setting.',
            'type'          => 'string',
            'required'      => true
        ],
        'code' =>  [
            'description'   => 'Unique identifier of the setting.',
            'type'          => 'string',
            'required'      => true
        ],
        'value' => [
            'description'   => 'Value to assign to the setting.',
            'type'          => 'string',
            'required'      => true
        ],
        'type' => [
            'description'   => 'Type of the values associated to the setting.',
            'type'          => 'string',
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
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm', 'access']
]);

$context = $providers['context'];

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

$context->httpResponse()
        ->status(201)
        ->send();