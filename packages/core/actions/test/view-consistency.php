<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'description'   => 'Validate a model view definition against its JSON schema.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'list.default'
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

$schema_ids = [
    'form' => 'urn:equal:json-schema:core:view.form',
    'list' => 'urn:equal:json-schema:core:view.list'
];

$view_type = $parts[0];

if(!isset($schema_ids[$view_type])) {
    throw new Exception('invalid_view_type', EQ_ERROR_INVALID_PARAM);
}

$schema_id = $schema_ids[$view_type];

$json = eQual::run('get', 'model_view', [
            'entity'    => $params['entity'],
            'view_id'   => $params['view_id']
        ],
        false,
        true
    );

if($json === false) {
    throw new Exception('malformed_view_json', EQ_ERROR_INVALID_CONFIG);
}

$validation = eQual::run('get', 'json-validate', [
    'json'      => $json,
    'schema_id' => $schema_id
]);

if(!($validation['result'] ?? false)) {
    // 'errors'    => $validation['errors'] ?? [],
    throw new Exception('invalid_view_json', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->status(204)
        ->send();

