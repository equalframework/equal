<?php

list($params, $providers) = eQual::announce([
    'description'   => 'This is the core_config_update-translation controller created with core_config_create-controller.',
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        "package"   => [
            "description"   => "name of the package of the entity",
            "type"      => "string",
            "required"  => true
        ],
        "entity"    => [
            "description"   => "name of the entity to edit",
            "type"      => "string",
            "required"  => true
        ],
        "lang"      => [
            "description"   => "lang of the translation",
            "type"          => "string",
            "required"      => true
        ],
        "payload"   => [
            "type"      => "text",
            "default"   => "{}"
        ],
        "create_lang"    => [
            "type"      => "boolean",
            "default"   => false
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
$entity = $params["entity"];
$lang = $params["lang"];
$payload = $params["payload"];
$create = $params["create_lang"];

// Checking if package exists
if(!file_exists(QN_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}

// Creating language folder
if(!is_dir(QN_BASEDIR."/packages/{$package}/i18n") && ($create==0) ){
        throw new Exception('no_translation_folder', QN_ERROR_INVALID_CONFIG);
}
if(!is_dir(QN_BASEDIR."/packages/{$package}/i18n/{$lang}") && ($create==0)){
        throw new Exception('language_does_not_exist_in_package', QN_ERROR_INVALID_CONFIG);
}
if(!is_dir(QN_BASEDIR."/packages/{$package}/i18n/{$lang}") && !mkdir(QN_BASEDIR."/packages/{$package}/i18n/{$lang}",true)) {
    throw new Exception('io_error', QN_ERROR_INVALID_CONFIG);
}

$parts = explode("\\",$entity);

$filename = array_pop($parts).".json";

$dir = implode("/",$parts);

if(!is_dir(QN_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}")) {
    if($create == 0) {
        throw new Exception('this_traduction_does_not_exist', QN_ERROR_INVALID_CONFIG);
    }
    mkdir(QN_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}",true);
}

$json = json_decode($payload,true);

if($json === null) {
    throw new Exception('payload_not_valid', QN_ERROR_INVALID_CONFIG);
}

$pretty = json_encode($json,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if(!$pretty) {
    throw new Exception('payload_not_valid', QN_ERROR_INVALID_CONFIG);
}

$f = fopen(QN_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}/{$filename}","w");

if(!$f) {
    throw new Exception('io_error', QN_ERROR_INVALID_CONFIG);
}

fputs($f,$pretty);

fclose($f);

$res = file_get_contents(QN_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}/{$filename}");

$context->httpResponse()
        ->body($pretty)
        ->status(200)
        ->send(); 