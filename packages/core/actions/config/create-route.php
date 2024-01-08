<?php

$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_create-route controller created with core_config_create-controller.',
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package'   => [ 
            'type' => 'string',
            'required'  => true,
            'selection' => array_values($packages)
        ],
        'file'      => [
            'type'  => 'string',
            'required'  => true
        ],
        'url'     => [
            'type'  => 'string',
            'required'  => true
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

$package = $params['package'];
$file = $params['file'];
$route = $params['url'];

if(!file_exists(QN_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}

$fr = QN_BASEDIR."/packages/{$package}/init/routes";

if(!is_dir($fr)) {
    mkdir($fr, 0777, true);
}

if(!file_exists($fr."/".$file)){
    if(strlen($file) < 9) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);
    if(strpos("0123456789", $file[0]) === false) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);
    if(strpos("0123456789", $file[1]) === false) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);
    if(strcmp("-", $file[2]) !== 0) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);
    if(substr_compare($file, ".json", -strlen(".json")) !== 0) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);
    if(strcmp(strtolower($file),$file) !== 0) throw new Exception('bad_route_filename', QN_ERROR_INVALID_CONFIG);

    // Create the file
    $f = fopen($fr."/".$file,"w");
    if(!$f) {
        throw new Exception('io_error', QN_ERROR_UNKNOWN);
    }
    fputs($f,"{}");
    fclose($f);
}

$data = json_decode(file_get_contents($fr."/".$file),true);

if($data === null) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}

foreach($data as $k=>$v) {
    if(strcmp($k,$route) === 0) throw new Exception('route_already_exists', QN_ERROR_INVALID_CONFIG);
}

$data[$route] = [];

file_put_contents($fr."/".$file,json_encode($data));

$res = file_get_contents($fr."/".$file);

$context->httpResponse()
        ->body(json_encode($res))
        ->status(200)
        ->send();