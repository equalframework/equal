<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
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
list($context) = [$providers['context']];

$parts = explode("\\",$params['entity']);
$parts_v = explode(".",$params['view_id']);

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
    throw new Exception("view_id_invalid",QN_ERROR_INVALID_PARAM);
}

if(strcmp($type, "form")!==0 && strcmp($type, "list")!==0 && strcmp($type, "search")!==0 && strcmp($type, "app")!==0 && strcmp($type, $package)!==0 ) {
    $test = strcmp($type, "list");
    throw new Exception("view_type_invalid",QN_ERROR_INVALID_PARAM);
}

$file = QN_BASEDIR."/packages/{$package}/views/{$entity}.{$type}.{$name}.json";

// Check if the view exists
if(file_exists($file)){
    throw new Exception('view_already_exists', QN_ERROR_INVALID_PARAM);
}

$nest = explode("/",$entity);
array_pop($nest);
$path = implode("/",$nest);

if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")){
    mkdir(QN_BASEDIR."/packages/{$package}/views/{$path}",0777,true);
    if(!is_dir(QN_BASEDIR."/packages/{$package}/views/{$path}")) {
        throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
    }
}

$f = fopen($file,"w");

if(!$f) {
    throw new Exception('file_access_denied', QN_ERROR_UNKNOWN);
}

if($type == "form" || $type == "search") {
    fputs($f,"{\"layout\" : {\"groups\" : []}}");
}
else {
    fputs($f,"{\"layout\" : {\"items\" : []}}");
}

fclose($f);

$result = file_get_contents($file);

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
