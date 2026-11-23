<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package of the new model.',
            'required'      => true
        ],
        'path' => [
            'type'          => 'string',
            'description'   => 'Relative path to the file inside `packages/{package}/`.',
            'required'      => true
        ],
        'type' => [
            'type'          => 'string',
            'description'   => 'Type of the UML data.',
            'selection'     => [
                'erd'
            ],
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'text',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$response_code = 200;

$package = $params["package"];

// Checking if package exists
if(!file_exists(EQ_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_PARAM);
}

// Checking if package exists
if(!file_exists(EQ_BASEDIR."/packages/{$package}/uml")) {
    throw new Exception('malformed_package', EQ_ERROR_INVALID_CONFIG);
}

$path_arr = explode('/', $params['path']);
$filename = array_pop($path_arr);
$path = implode("/", $path_arr);

$path = str_replace("..", "", $path);

$extension = ".{$params["type"]}.json";

if(substr($filename, -strlen($extension)) != $extension) {
    $filename = $filename.$extension;
}

if(!file_exists(EQ_BASEDIR."/packages/{$package}/uml/{$path}/{$filename}")) {
    throw new Exception('io_error', EQ_ERROR_INVALID_CONFIG);
}

$context
    ->httpResponse()
    ->body(file_get_contents(EQ_BASEDIR."/packages/{$package}/uml/{$path}/{$filename}"))
    ->status(200)
    ->send();
