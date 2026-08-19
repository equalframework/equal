<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'type'          => 'do',
    'name'          => 'test_dashboard-consistency',
    'package_name'  => 'core',
    'description'   => 'Validate a dashboard view definition against its JSON schema.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\alert\\Message\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the dashboard view <dashboard.name>.',
            'type'          => 'string',
            'default'       => 'dashboard.default'
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

$parts = explode('.', $params['view_id'], 2);
if(count($parts) !== 2 || !isset($parts[0]) || !strlen($parts[0]) || !isset($parts[1]) || !strlen($parts[1])) {
    throw new Exception('invalid_view_id', EQ_ERROR_INVALID_PARAM);
}

if($parts[0] !== 'dashboard') {
    throw new Exception('invalid_dashboard_view_type', EQ_ERROR_INVALID_PARAM);
}

$json = eQual::run('get', 'model_view', [
            'entity'    => $params['entity'],
            'view_id'   => $params['view_id']
        ],
        false,
        true
    );

if($json === false) {
    throw new Exception('malformed_dashboard_json', EQ_ERROR_INVALID_CONFIG);
}

$validation = eQual::run('get', 'json-validate', [
    'json'      => $json,
    'schema_id' => 'urn:equal:json-schema:core:view.dashboard'
]);

if(!($validation['result'] ?? false)) {
    throw new Exception('invalid_dashboard_json', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->status(204)
        ->send();
