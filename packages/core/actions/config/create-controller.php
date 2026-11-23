<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create a new controller using a given name.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package of the controller',
            'required'      => true
        ],
        'controller_type' => [
            'type'          => 'string',
            'description'   => 'Type of controller',
            'selection'     => ["do","get"],
            'required'      => true
        ],
        'controller_name' => [
            'type'          => 'string',
            'description'   => 'Name of the controller with the path inside of the package (ex : user_create)',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$type = $params['controller_type'];

$package = $params['package'];

if(!file_exists(EQ_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}

if(strcmp($type,"do") === 0) $subdir = "actions";
else $subdir = "data";

if(!file_exists(EQ_BASEDIR."/packages/{$package}/{$subdir}")) {
    throw new Exception('missing_classes_dir', EQ_ERROR_INVALID_CONFIG);
}

$part = explode("_",$params["controller_name"]);
$controller_name = array_pop($part);
$controller_path = implode("/",$part);

$fullpath = EQ_BASEDIR."/packages/{$package}/{$subdir}/{$controller_path}/{$controller_name}.php";

if(file_exists($fullpath)) {
    throw new Exception('controller_already_exists', EQ_ERROR_INVALID_CONFIG);
}

$dir = EQ_BASEDIR."/packages/{$package}/{$subdir}/{$controller_path}/";
$d = mkdir($dir, 0777, true);

$f = fopen($fullpath,"w");

if(!$f) {
    throw new Exception('io_error', EQ_ERROR_UNKNOWN);
}

$template = <<<EOT
<?php

list(\$params, \$providers) = eQual::announce([
    'description'   => 'This is the {$package}_{$params["controller_name"]} controller created with core_config_create-controller.',
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [

    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['users']
    ],
    'providers'         => ['context']
]);
/**
 * @var \\equal\\php\\context  Context
 */
\$context = \$providers['context'];

// controller logic goes here

\$context->httpResponse()
        ->status(200)
        ->send();
EOT;

fputs($f, $template);
fclose($f);

$result = file_get_contents($fullpath);

$context
    ->httpResponse()
    ->status(201)
    ->send();