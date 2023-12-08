<?php

use function PHPUnit\Framework\stringStartsWith;

list($params, $providers) = eQual::announce([
    'description' => 'This is the core_config_init-data controller created with core_config_create-controller.',
    'response' => [
        'charset' => 'utf-8',
        'content-type' => 'application/json',
        'accept-origin' => [
            0 => '*',
        ],
    ],
    'params' => [
        'package' => [
            'description' => 'Package from where you want to get the initial data files',
            'help' => '',
            'type' => 'string',
            'usage' => 'orm/package',
            'required' => true,
            'default' => 'core',
        ],
    ],
    'access' => [
        'visibility' => 'protected',
        'groups' => [
            0 => 'users',
        ],
    ],
    'providers' => [
        0 => 'context',
    ],
]);
/** @var \equal\php\context Context */
$context = $providers['context'];

$package =  equal::run("do","sanitize_path",["path" => $params['package'], "name_only"=>true]) ;


if(!is_dir(QN_BASEDIR."/packages/$package")) {
    throw new Exception("package does not exists ",QN_ERROR_INVALID_PARAM);
}

$init_file_dir = QN_BASEDIR."/packages/$package/init/data";

if(!is_dir($init_file_dir)) {
    $context->httpResponse()
    ->body("{}")
    ->status(200)
    ->send();
    die();
}

$filenames = flattenFolder($init_file_dir);

$res = [];

foreach($filenames as $file) {
    $res[$file] = json_decode(file_get_contents("$init_file_dir/$file"),true);
}

// controller logic goes here
$context->httpResponse()
    ->body(json_encode($res,JSON_UNESCAPED_SLASHES))
    ->status(200)
    ->send();


function flattenFolder(string $path,string $suffix = "") {
    $res = [];
    $scan = scandir($path);
    foreach($scan as $item) {
        if(str_starts_with($item,".")) {
            continue;
        }
        if(str_ends_with($item,".json")) {
            $res[] = "$suffix$item";
            continue;
        }
        if(is_dir("$path/$item")) {
            $res = array_merge($res,flattenFolder("$path/$item","$suffix$item/"));
        }
    }
    return $res;
}