<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'description'   => 'Validate a package manifest file against its JSON schema.',
    'params'        => [
        'package' =>  [
            'description'   => 'Full name of the package for which the manifest must be tested.',
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

$schema_id = 'urn:equal:json-schema:core:package.manifest';
$manifest_filename = EQ_BASEDIR . '/packages/' . $params['package'] . '/manifest.json';

if(!file_exists($manifest_filename)) {
    throw new Exception('missing_manifest', EQ_ERROR_INVALID_PARAM);
}

$json = file_get_contents($manifest_filename);

if($json === false) {
    throw new Exception('manifest_not_accessible', EQ_ERROR_INVALID_CONFIG);
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

