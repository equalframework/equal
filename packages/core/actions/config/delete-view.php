<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Saves a representation of a view to a json file.",
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
        ]
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
list($context) = [$providers['context']];

$parts = explode("\\", $params['entity']);
$parts_v = explode(".", $params['view_id']);

$package = array_shift($parts);
$entity = implode("/", $parts);

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
    throw new Exception("view_id_invalid", QN_ERROR_INVALID_PARAM);
}

if (strcmp($name,"default") === 0) {
    throw new Exception("cant_delete_default_view", QN_ERROR_INVALID_PARAM);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$type}.{$name}.json";

// Check if the view exists or not
if(!file_exists($file)){
    throw new Exception('unknown_view', QN_ERROR_INVALID_PARAM);
}

$f = unlink($file);

if(!$f) {
    throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();
