<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use function PHPUnit\Framework\stringStartsWith;

list($params, $providers) = eQual::announce([
    'description'   => "save a representation of a view to a json file",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
        'package' => [
            'description' => 'Name of the entity.',
            'type' => 'string',
            'required' => true
        ],
        'type' => [
            'description' => 'type of init-data you want to gather',
            'help' => '',
            'type' => 'string',
            'default' => 'init',
            'selection' => [
                'init','demo'
            ]
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

if( ($decoded = json_decode($params['payload'],true)) === null) {
    throw new Error("payload not valid",QN_ERROR_INVALID_PARAM);
}

$package = equal::run("do","sanitize_path",["path" => $params['package'], "name_only"=>true]);


$trad = [
    "init" => "data",
    "demo" => "demo"
];

$path = QN_BASEDIR."/packages/$package/init/{$trad[$params['type']]}";

if(!is_dir($path)) {
    throw new Exception("malformed package",QN_ERROR_INVALID_CONFIG);
}

$files = flattenFolder($path);

$backups = [];

foreach($files as $file) {
    unlink($path.'/'.$file.'.bak');
    $res = rename($path.'/'.$file,$path.'/'.$file.'.bak');
    if(!$res) {
        throw new Exception("io error",QN_ERROR_INVALID_CONFIG);
    }
    $backups[] = $path.'/'.$file.'.bak';
}


foreach($decoded as $file => $content) {
    
    $sanitized_filename = equal::run("do","sanitize_path",["path" => $file, "name_only"=>false]);
    $f = fopen($path.'/'.$sanitized_filename,"w");
    if(!$f) {
        throw new Exception("io error",QN_ERROR_INVALID_CONFIG);
    }
    $json = json_encode($content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    if(!$json) {
        throw new Exception("encoding error",QN_ERROR_INVALID_CONFIG);
    }
    fputs($f,$json);
    fclose($f);
}

foreach($backups as $file) {
    unlink($file);
}


$context->httpResponse()
        ->status(201)
        ->send();


function flattenFolder(string $path,string $suffix = ""):array {
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