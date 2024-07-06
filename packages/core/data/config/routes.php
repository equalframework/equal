<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_routes controller created with core_config_create-controller.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        "package" => [
            "type"          => "string",
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            "required"      => true,
            "description"   => "Package to inspect.",
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

$dir = EQ_BASEDIR."/packages/{$package}/init/routes";

$result = [];

if(is_dir($dir)) {
    $files = scandir($dir);

    foreach($files as $i => $file) {
        // Cleaning files
        if(strcmp($file, '.') === 0 || strcmp($file, '..') === 0) {
            continue;
        }

        $result = array_merge($result, [$file => json_decode(file_get_contents($dir."/".$file))]);
    }
}

$context->httpResponse()
        ->body($result)
        ->send();
