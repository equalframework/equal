<?php

list($params, $providers) = announce([
    'description'   => "save a representation of a view to a json file",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
        'entity' => [
            'description' => 'name of the entity',
            'type' => 'string',
            'required' => true
        ],
        'viewid' => [
            'description' => 'id of the view',
            'type' => 'string',
            'required' => true
        ],
        'payload' =>  [
            'description'   => 'view definition .',
            'type'          => 'text',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

$parts = explode("\\",$params['entity']);

$package = array_shift($parts);
$entity = implode("/",$parts);

$payload = $params['payload'];

// Checking if package exists
if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$params[viewid]}.json";

// Check if the view exists
if(!file_exists($file)){
    throw new Exception('missing_view_file', QN_ERROR_UNKNOWN);
}

// Checking if json is conform
if( ($payload_decoded = json_decode($payload, true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

$pretty_payload = json_encode($payload_decoded, JSON_PRETTY_PRINT);

$IOERROR = file_put_contents($file,$pretty_payload);

if(!$IOERROR){
    throw new Exception("IOError : check file rights", QN_ERROR_INVALID_CONFIG);
}

$result = file_get_contents($file);

$context->httpResponse()
        ->body("{$result}")
        ->send();