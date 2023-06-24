<?php

list($params, $providers) = announce([
    'description'   => "Provide a unique identifier of the current git revision.\n".
                       "This script assumes the current installation is versioned using git.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'params'    => [
    ],
    'providers' =>  ['context']
]);

list(   $context,
        $head_path,
        $index_path
) = [
    $providers['context'],
    '../.git/HEAD',
    '../.git/index'
];

if(!file_exists($head_path)) {
    throw new Exception('git HEAD not found', QN_ERROR_INVALID_CONFIG);
}

if(!file_exists($index_path)) {
    throw new Exception('git index not found', QN_ERROR_INVALID_CONFIG);
}

$files = explode(' ', file_get_contents($head_path));

if(!$files || !count($files)) {
    throw new Exception('no files found in git index', QN_ERROR_INVALID_CONFIG);
}

$file = trim($files[1]);

$file_path = "../.git/$file";

if(!file_exists($file_path)) {
    throw new Exception('inconsistent git index', QN_ERROR_INVALID_CONFIG);
}


// read first bytes from current branch revision hash
$hash = substr(file_get_contents($file_path), 0, 7);
$time = filemtime($index_path);
$date = date("Y.m.d", $time);


$context->httpResponse()->body(['revision' => "$date.$hash"])->send();