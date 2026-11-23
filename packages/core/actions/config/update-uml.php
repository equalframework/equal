<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package of the new model',
            'required'      => true
        ],
        'filename' => [
            'type'          => 'string',
            'description'   => 'relative path to the file from packages/{package}/uml',
            'required'      => true
        ],
        'payload' => [
            'type'          => 'text',
            'description'   => 'value of the field',
            'required'      => true
        ],
        'type' => [
            'type'          => 'string',
            'description'   => 'Type of the UML data',
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

$package = $params['package'];

if(!file_exists(EQ_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_PARAM);
}

if(!file_exists(EQ_BASEDIR."/packages/{$package}/uml")) {
    if(!mkdir(EQ_BASEDIR."/packages/{$package}/uml", 0775, true)) {
        throw new Exception('io_error'.EQ_BASEDIR."/packages/{$package}/uml/{$path}", EQ_ERROR_INVALID_CONFIG);
    }
}

$filepath = str_replace(['..', '/'], ['', '_'], $params['filename']);

$parts = explode("_", $filepath);
$filename = array_pop($parts);

$path = implode('/', $parts);

$extension = ".{$params["type"]}.json";

if(substr($filename, -strlen($extension)) != $extension) {
    $filename = $filename.$extension;
}

$final_path = EQ_BASEDIR."/packages/{$package}/uml/" . ( (strlen($path))?($path.'/'):'' );

if(!is_dir($final_path)) {
    if(!mkdir($final_path, 0775, true)) {
        throw new Exception('io_error', EQ_ERROR_INVALID_CONFIG);
    }
}

if(!file_exists($final_path.$filename)) {
    $response_code = 201;
}

// store payload
$f = fopen($final_path.$filename, "w");
if(!$f) {
    throw new Exception('io_error', EQ_ERROR_INVALID_CONFIG);
}
fputs($f, $params['payload']);
fclose($f);

$result = file_get_contents($final_path.$filename);

$context
    ->httpResponse()
    ->body($result)
    ->status($response_code)
    ->send();
