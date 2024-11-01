<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "save a representation of a view to a json file",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
        'entity' => [
            'description' => 'Name of the entity.',
            'type' => 'string',
            'required' => true
        ],
        'view_id' => [
            'description' => 'Identifier of the view.',
            'type' => 'string',
            'required' => true
        ],
        'payload' =>  [
            'description'   => 'View definition (JSON).',
            'type'          => 'text',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm']
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$parts = explode("\\",$params['entity']);

$package = array_shift($parts);
$entity = implode("/", $parts);

// Checking if package exists
if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$params['view_id']}.json";

// Check if the view exists
if(!file_exists($file)){
    throw new Exception('missing_view_file', QN_ERROR_UNKNOWN);
}

// Checking if json is conform
if( ($payload_decoded = json_decode($params['payload'], true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

$json = json_encode($payload_decoded, JSON_PRETTY_PRINT);

$result = file_put_contents($file, $json);

if(!$result){
    throw new Exception("file_update_denied", QN_ERROR_INVALID_CONFIG);
}

$result = file_get_contents($file);

$context->httpResponse()
        ->status(204)
        ->send();
