<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "save a representation of a view to a json file",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Name of the entity.',
            'required'      => true
        ],
        'view_id' => [
            'type'          => 'string',
            'description'   => 'Identifier of the view.',
            'required'      => true
        ],
        'payload' => [
            'type'          => 'text',
            'description'   => 'View definition (JSON).',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$parts = explode("\\",$params['entity']);

$package = array_shift($parts);
$entity = implode("/", $parts);

// Checking if package exists
if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', EQ_ERROR_INVALID_CONFIG);
}

$file = EQ_BASEDIR."/packages/{$package}/views/{$entity}.{$params['view_id']}.json";

// Check if the view exists
if(!file_exists($file)){
    throw new Exception('missing_view_file', EQ_ERROR_UNKNOWN);
}

// Checking if json is conform
if( ($payload_decoded = json_decode($params['payload'], true)) === null) {
    throw new Exception("malformed_view_schema", EQ_ERROR_INVALID_CONFIG);
}

$json = json_encode($payload_decoded, JSON_PRETTY_PRINT);

$result = file_put_contents($file, $json);

if(!$result){
    throw new Exception("file_update_denied", EQ_ERROR_INVALID_CONFIG);
}

$result = file_get_contents($file);

$context
    ->httpResponse()
    ->status(204)
    ->send();
