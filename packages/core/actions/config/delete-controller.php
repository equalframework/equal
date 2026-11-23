<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'This is the core_config_delete-controller controller created with core_config_create-controller.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package of the controller',
            'required'      => true
        ],
        'controller_type' => [
            'type'          => 'string',
            'description'   => 'Type of controller',
            'selection'     => ["do","get"],
            'required'      => true
        ],
        'controller_name' => [
            'type'          => 'string',
            'description'   => 'Name of the controller with the path inside of the package (ex : user_create)',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['users']
    ],
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);
/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

// controller logic goes here
$type = $params['controller_type'];

$package = $params['package'];

if(!file_exists(EQ_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}

if(strcmp($type,"do") === 0) $subdir = "actions";
else $subdir = "data";

if(!file_exists(EQ_BASEDIR."/packages/{$package}/{$subdir}")) {
    throw new Exception('missing_classes_dir', EQ_ERROR_INVALID_CONFIG);
}

$part = explode("_",$params["controller_name"]);
$controller_name = array_pop($part);
$controller_path = implode("/",$part);

$fullpath = EQ_BASEDIR."/packages/{$package}/{$subdir}/{$controller_path}/{$controller_name}.php";

if(!file_exists($fullpath)) {
    throw new Exception('controller_does_not_exists', EQ_ERROR_INVALID_CONFIG);
}

if(!unlink($fullpath)) {
    throw new Exception('io_error', EQ_ERROR_UNKNOWN);
}


$context
    ->httpResponse()
    ->status(204)
    ->send();