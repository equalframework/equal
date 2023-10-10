<?php

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_delete-package controller created with core_config_create-controller.',
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package to be deleted.',
            'type'          => 'string',
            'required'      => true
        ],
        /*
        'true_delete' => [
            'description'   => 'enable true deletion of the folder',
            'type'          => 'boolean',
            'default'       => false
        ]
        */
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

if(strcmp($params['package'],"core")===0) {
    throw new Exception('core_is_undeletable', QN_ERROR_INVALID_PARAM);
}

$dir = QN_BASEDIR.'/packages/'.$params['package'];


// controller logic goes here
if(!is_dir($dir)) {
    throw new Exception('package_does_not_exists', QN_ERROR_INVALID_PARAM);
}

if(!delTree($dir)) {
    throw new Exception('io_error', QN_ERROR_INVALID_PARAM);
}

/*
if($params['true_delete']) {
    if(!delTree($dir)) {
        throw new Exception('io_error', QN_ERROR_INVALID_PARAM);
    }
} else {
    if(!is_dir(QN_BASEDIR."/_trash/packages/".$params['package'])) {
        if(!mkdir(QN_BASEDIR."/_trash/packages/".$params['package'],0777,true)){
            throw new Exception('io_error(mkdir)', QN_ERROR_INVALID_PARAM);
        }
    }
    if(!rename($dir,QN_BASEDIR."/_trash/packages/".$params['package']."/".time())) {
        throw new Exception('io_error', QN_ERROR_INVALID_PARAM);
    }
}
*/

$context->httpResponse()
        ->status(201)
        ->send();


function delTree($dir) {

    $files = array_diff(scandir($dir), array('.','..'));
    
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    
    return rmdir($dir);
}