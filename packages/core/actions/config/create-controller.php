<?php

list($params, $providers) = eQual::announce([
    'description'   => "Attempts to create a new controller using a given name.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package of the controller',
            'type'          => 'string',
            'required'      => true
        ],
        'controller_type' => [
            'description'   => 'Type of controller',
            'type'          => 'string',
            "required"      => true,
            "selection"     => ["do","get"]
        ],
        'controller_name' => [
            'description'   => 'Name of the controller with the path inside of the package (ex : user_create)',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm', 'access']
]);

$context = $providers['context'];

$type = $params['controller_type'];

$package = $params['package'];

if(!file_exists(QN_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}

if(strcmp($type,"do") === 0) $subdir = "actions";
else $subdir = "data";

if(!file_exists(QN_BASEDIR."/packages/{$package}/{$subdir}")) {
    throw new Exception('missing_classes_dir', QN_ERROR_INVALID_CONFIG);
}

$part = explode("_",$params["controller_name"]);
$controller_name = array_pop($part);
$controller_path = implode("/",$part);

$fullpath = QN_BASEDIR."/packages/{$package}/{$subdir}/{$controller_path}/{$controller_name}.php";

if(file_exists($fullpath)) {
    throw new Exception('controller_already_exists', QN_ERROR_INVALID_CONFIG);
}

$dir = QN_BASEDIR."/packages/{$package}/{$subdir}/{$controller_path}/";
$d = mkdir($dir, 0777, true);

$f = fopen($fullpath,"w");

if(!$f) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
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

$context->httpResponse()
        ->status(201)
        ->body($result."\n")
        ->send();