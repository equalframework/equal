<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'type'          => 'do',
    'name'          => 'test_route-consistency',
    'package_name'  => 'core',
    'description'   => 'Validate an API route definition file against its JSON schema.',
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package the route file relates to (e.g. \'core\').',
            'type'          => 'string',
            'required'      => true
        ],
        'file' =>  [
            'description'   => 'Route file name in the package init/routes directory.',
            'type'          => 'string',
            'required'      => true
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

if(basename($params['file']) !== $params['file'] || !preg_match('/^[a-z0-9_.-]+\.json$/i', $params['file'])) {
    throw new Exception('invalid_route_file', EQ_ERROR_INVALID_PARAM);
}

$route_filename = EQ_BASEDIR . "/packages/{$params['package']}/init/routes/{$params['file']}";

if(!file_exists($route_filename)) {
    throw new Exception('missing_route_file', EQ_ERROR_INVALID_PARAM);
}

$json = file_get_contents($route_filename);

if($json === false) {
    throw new Exception('route_not_accessible', EQ_ERROR_INVALID_CONFIG);
}

$validation = eQual::run('get', 'json-validate', [
    'json'      => $json,
    'schema_id' => 'urn:equal:json-schema:core:api.route'
]);

if(!($validation['result'] ?? false)) {
    throw new Exception('invalid_route_json', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->status(204)
        ->send();
