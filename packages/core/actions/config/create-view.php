<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'description'   => "Create a new empty view as a json file, for a given entity.",
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
        'view_id' => [
            'description' => 'id of the view',
            'type' => 'string',
            'required' => true
        ],
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

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

$parts = explode(".",$params['view_id']);

$type = array_shift($parts);
$name = array_shift($parts);

if(count($parts) > 0) {
    throw new Exception("view_id_invalid", QN_ERROR_INVALID_PARAM);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$type}.{$name}.json";

// Check if the view exists
if(file_exists($file)){
    throw new Exception('view_already_exists', QN_ERROR_INVALID_PARAM);
}

$path = dirname($entity);

if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")){
    mkdir(QN_BASEDIR."/packages/{$package}/views/{$path}", 0777, true);
    if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")) {
        throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
    }
}

if(!file_put_contents($file, $type === "form" || $type === "search"
    ? "{\"layout\" : {\"groups\" : []}}"
    : "{\"layout\" : {\"items\" : []}}")) {
    throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
}

$result = file_get_contents($file);

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
