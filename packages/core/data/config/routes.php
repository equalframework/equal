<?php

$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_routes controller created with core_config_create-controller.',
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        "package" => [
            "decription" => "package to inspect",
            "type" => "string",
            'selection' => array_combine(array_values($packages), array_values($packages)),
            "required" => true
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

$package = $params["package"];

$dir = QN_BASEDIR."/packages/{$package}/init/routes";

if(!is_dir($dir)) {
    throw new Exception('no_routes_dir', QN_ERROR_INVALID_PARAM);
}

$files = scandir($dir);

$tree = [];

foreach($files as $i => $file) {
    // Cleaning files
    if(strcmp($file,".") === 0 || strcmp($file,"..") === 0) continue;

    $tree = array_merge($tree,[$file => json_decode(file_get_contents($dir."/".$file))]);
}

$res = json_encode($tree);

$context->httpResponse()
        ->body($res)
        ->status(200)
        ->send();