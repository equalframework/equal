<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'This is the core_config_update-translation controller created with core_config_create-controller.',
    'params'        => [
        "package" => [
            "description"   => "name of the package of the entity",
            "type"          => "string",
            "required"      => true
        ],
        "entity" => [
            "description"   => "name of the entity to edit",
            "type"          => "string",
            "required"      => true
        ],
        "lang" => [
            "description"   => "lang of the translation",
            "type"          => "string",
            "required"      => true
        ],
        "payload" => [
            "type"          => "string",
            "usage"         => "text/json",
            "default"       => "{}"
        ],
        "create_lang" => [
            "type"          => "boolean",
            "default"       => false
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['users']
    ],
    'response'      => [
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);
/**
 * @var \equal\php\context  Context
 */
['context' => $context] = $providers;

$package = $params["package"];
$entity = $params["entity"];
$lang = $params["lang"];
$payload = $params["payload"];
$create = $params["create_lang"];

// Checking if package exists
if(!file_exists(EQ_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}

$parts = explode("\\", $entity);

$filename = array_pop($parts) . ".json";

$dir = implode("/", $parts);

if(!is_dir(EQ_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}")) {
    mkdir(EQ_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}", true);
}

$json = json_decode($payload, true);

if($json === null) {
    throw new Exception('payload_not_valid', EQ_ERROR_INVALID_CONFIG);
}

$output = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if(!$output) {
    throw new Exception('payload_not_valid', EQ_ERROR_INVALID_CONFIG);
}

file_put_contents(EQ_BASEDIR."/packages/{$package}/i18n/{$lang}/{$dir}/{$filename}", $output);

$context->httpResponse()
        ->body($output)
        ->status(200)
        ->send();
