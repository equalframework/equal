<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\fs\FSManipulator;

[$params, $providers] = eQual::announce([
    'description'   => "Save a representation of a view to a json file.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the entity.',
            'required'      => true
        ],
        'type' => [
            'type'          => 'string',
            'description'   => 'Type of requested init-data (folder).',
            'selection'     => [
                'init',
                'demo'
            ],
            'default'       => 'init'
        ],
        'payload' => [
            'type'          => 'text',
            'description'   => 'View definition (JSON).',
            'required'      => true
        ]
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

if( ($decoded = json_decode($params['payload'], true)) === null) {
    throw new Exception("invalid_payload", EQ_ERROR_INVALID_PARAM);
}

$package = $params['package'];

$packages = equal::run("get", "config_packages");

if(!in_array($package, $packages)) {
    throw new Exception("unknown_package", EQ_ERROR_INVALID_PARAM);
}

$folder = ([
        "init" => "data",
        "demo" => "demo"
    ])[$params['type']];

$dir = EQ_BASEDIR."/packages/$package/init/$folder";

if(!is_dir($dir)) {
    throw new Exception("missing_directory", EQ_ERROR_INVALID_CONFIG);
}

$files = FSManipulator::getDirFlatten($dir, ['json']);

$backups = [];

foreach($files as $file) {
    unlink($dir.'/'.$file.'.bak');
    $res = rename($dir.'/'.$file, $dir.'/'.$file.'.bak');
    if(!$res) {
        throw new Exception("io_error",EQ_ERROR_INVALID_CONFIG);
    }
    $backups[] = $dir.'/'.$file.'.bak';
}

foreach($decoded as $file => $content) {
    $sanitized_filename = FSManipulator::getSanitizedPath($file);
    $f = fopen($dir.'/'.$sanitized_filename, "w");
    if(!$f) {
        throw new Exception("io_error", EQ_ERROR_INVALID_CONFIG);
    }
    $json = json_encode($content, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!$json) {
        throw new Exception("encoding_error", EQ_ERROR_INVALID_CONFIG);
    }
    fputs($f, $json);
    fclose($f);
}

foreach($backups as $file) {
    unlink($file);
}

$context
    ->httpResponse()
    ->status(201)
    ->send();
