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
        
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

$parts = explode("\\",$params['entity']);
$parts_v = explode(".",$params['viewid']);

$package = array_shift($parts);
$entity = implode("/",$parts);

// Checking if package exists
if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
}

$type = array_shift($parts_v);
$name = array_shift($parts_v);

if(count($parts_v) > 0) {
    throw new Exception("view_id_invalid",QN_ERROR_INVALID_PARAM);
}

if (strcmp($name,"default") === 0) {
    throw new Exception("cant_delete_default_view",QN_ERROR_INVALID_PARAM);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$type}.{$name}.json";

// Check if the view exists or not
if(!file_exists($file)){
    throw new Exception('view_does_not_exists', QN_ERROR_INVALID_PARAM);
}

$f = unlink($file);

if(!$f) {
    throw new Exception('IOERROR', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();