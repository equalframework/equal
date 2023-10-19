<?php

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_delete-model controller created with core_config_create-controller.',
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package of the new model',
            'type'          => 'string',
            'required'      => true
        ],
        'model' =>  [
            'description'   => 'Name of the model to be created (must be unique).',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['users']
    ],
    'providers'         => ['context']
]);
/**
 * @var \equal\php\context  Context
 */
$context = $providers['context'];

// controller logic goes here
$package = $params['package'];

if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/classes")) {
    throw new Exception('missing_classes_dir', QN_ERROR_INVALID_CONFIG);
}

$model_path = $params['model'];

$parts = explode("\\", $model_path);

$name = array_pop($parts);


$namespace = implode("\\", $parts);

if(strlen($name) <= 0){
    throw new Exception('empty_model_name', QN_ERROR_INVALID_PARAM);
}

if(!ctype_upper(mb_substr($name, 0, 1))){
    throw new Exception('broken_naming_convention', QN_ERROR_INVALID_PARAM);
}

// Verification de la non existence du model
$file = QN_BASEDIR."/packages/{$package}/classes/{$model_path}.class.php";

if(!file_exists($file)) {
    throw new Exception('model_does_not_exists', QN_ERROR_INVALID_PARAM);
}

if(!unlink($file)) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}


$context->httpResponse()
        ->status(200)
        ->send();