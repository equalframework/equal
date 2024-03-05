<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\fs\FSManipulator;

list($params, $providers) = eQual::announce([
    'description'   => "Save a representation of a view to a json file.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
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
            'default'       => 'init',
        ],
        'payload' =>  [
            'description'   => 'View definition (JSON).',
            'type'          => 'text',
            'required'      => true
        ]
    ],
    'providers'     => ['context']
]);

/** @var \equal\php\context Context */
list($context) = [$providers['context']];

if( ($decoded = json_decode($params['payload'], true)) === null) {
    throw new Exception("invalid_payload", QN_ERROR_INVALID_PARAM);
}

$package = $params['package'];

$packages = equal::run("get", "config_packages");

if(!in_array($package, $packages)) {
    throw new Exception("unknown_package", QN_ERROR_INVALID_PARAM);
}

$folder = ([
        "init" => "data",
        "demo" => "demo"
    ])[$params['type']];

$dir = QN_BASEDIR."/packages/$package/init/$folder";

if(!is_dir($dir)) {
    throw new Exception("missing_directory", QN_ERROR_INVALID_CONFIG);
}

$files = FSManipulator::getDirFlatten($dir, ['json']);

$backups = [];

foreach($files as $file) {
    unlink($dir.'/'.$file.'.bak');
    $res = rename($dir.'/'.$file, $dir.'/'.$file.'.bak');
    if(!$res) {
        throw new Exception("io_error",QN_ERROR_INVALID_CONFIG);
    }
    $backups[] = $dir.'/'.$file.'.bak';
}

foreach($decoded as $file => $content) {
    $sanitized_filename = FSManipulator::getSanitizedPath($file);
    $f = fopen($dir.'/'.$sanitized_filename, "w");
    if(!$f) {
        throw new Exception("io_error", QN_ERROR_INVALID_CONFIG);
    }
    $json = json_encode($content, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!$json) {
        throw new Exception("encoding_error", QN_ERROR_INVALID_CONFIG);
    }
    fputs($f, $json);
    fclose($f);
}

foreach($backups as $file) {
    unlink($file);
}

$context->httpResponse()
        ->status(201)
        ->send();
