<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'type'          => 'do',
    'name'          => 'test_menu-consistency',
    'package_name'  => 'core',
    'description'   => 'Validate a menu definition against its JSON schema.',
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package the menu relates to (e.g. \'core\').',
            'type'          => 'string',
            'required'      => true
        ],
        'menu_id' =>  [
            'description'   => 'The identifier of the menu <name>.',
            'type'          => 'string',
            'default'       => 'list'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
$context = $providers['context'];

if(!preg_match('/^[a-z0-9_-]+$/i', $params['package'])) {
    throw new Exception('invalid_package', EQ_ERROR_INVALID_PARAM);
}

if(!preg_match('/^[a-z0-9_.-]+$/i', $params['menu_id']) || strpos($params['menu_id'], '..') !== false) {
    throw new Exception('invalid_menu_id', EQ_ERROR_INVALID_PARAM);
}

$menu_filename = EQ_BASEDIR . "/packages/{$params['package']}/views/menu.{$params['menu_id']}.json";

if(!file_exists($menu_filename)) {
    throw new Exception('missing_menu', EQ_ERROR_INVALID_PARAM);
}

$json = file_get_contents($menu_filename);

if($json === false) {
    throw new Exception('menu_not_accessible', EQ_ERROR_INVALID_CONFIG);
}

$validation = eQual::run('get', 'json-validate', [
    'json'      => $json,
    'schema_id' => 'urn:equal:json-schema:core:menu'
]);

if(!($validation['result'] ?? false)) {
    throw new Exception('invalid_menu_json', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->status(204)
        ->send();
