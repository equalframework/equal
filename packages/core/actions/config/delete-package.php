<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'This is the core_config_delete-package controller created with core_config_create-controller.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package to be deleted.',
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

if(strcmp($params['package'],"core") === 0) {
    throw new Exception('core_is_undeletable', EQ_ERROR_INVALID_PARAM);
}

$dir = EQ_BASEDIR.'/packages/'.$params['package'];


// controller logic goes here
if(!is_dir($dir)) {
    throw new Exception('package_does_not_exists', EQ_ERROR_INVALID_PARAM);
}

if(!delTree($dir)) {
    throw new Exception('io_error', EQ_ERROR_INVALID_PARAM);
}

$context
    ->httpResponse()
    ->status(201)
    ->send();


function delTree($dir) {

    $files = array_diff(scandir($dir), array('.','..'));

    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}