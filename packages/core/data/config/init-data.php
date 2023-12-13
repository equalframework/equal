<?php

use equal\fs\FSManipulator;

list($params, $providers) = eQual::announce([
    'description' => 'This is the core_config_init-data controller created with core_config_create-controller.',
    'response' => [
        'charset'       => 'utf-8',
        'content-type'  => 'application/json',
        'accept-origin' => ['*'],
    ],
    'params' => [
        'package' => [
            'type'          => 'string',
            'usage'         => 'orm/package',
            'description'   => 'Package for which initial data files are requested.',
            'default'       => 'core',
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
    ],
    'access' => [
        'visibility'    => 'protected',
        'groups'        => ['users'],
    ],
    'providers' => ['context'],
]);

/** @var \equal\php\context Context */
$context = $providers['context'];

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

$result = [];

$files = FSManipulator::getDirFlatten($dir, ['json']);

foreach($files as $file) {
    $result[$file] = json_decode(file_get_contents("$dir/$file"), true);
}

$context->httpResponse()
    ->body($result)
    ->status(200)
    ->send();
