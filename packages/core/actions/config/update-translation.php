<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description' => 'Update or create translation file for an entity',
    'params' => [
        'package' => [
            'type'        => 'string',
            'required'    => true,
            'description' => 'Name of the package.'
        ],
        'entity' => [
            'type'        => 'string',
            'required'    => true,
            'description' => 'Entity name (class or identifier).'
        ],
        'lang' => [
            'type'        => 'string',
            'required'    => true,
            'description' => 'Language code (e.g., en, fr, en_US).'
        ],
        'payload' => [
            'type'        => 'string',
            'usage'       => 'text/json',
            'default'     => '{}',
            'description' => 'JSON string with translation data.'
        ],
        'create_lang' => [
            'type'        => 'boolean',
            'default'     => false,
            'description' => 'Create language directory if missing.'
        ]
    ],
    'access' => [
        'visibility' => 'protected',
        'groups' => ['users']
    ],
    'providers' => ['context']
]);

/** @var \equal\php\context $context */
['context' => $context] = $providers;

$package = $params['package'];
$entity  = $params['entity'];
$lang    = $params['lang'];
$payload = $params['payload'];
$create  = $params['create_lang'];

// 1) Validate params

$package_dir = EQ_BASEDIR . "/packages/{$package}";
if(!is_dir($package_dir)) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}

// (ex: fr, fr_BE, en_US)
if(!preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $lang)) {
    throw new Exception('invalid_lang_format', EQ_ERROR_INVALID_CONFIG);
}

// entity safety
if(!preg_match('/^[A-Za-z0-9_\\\\]+$/', $entity)) {
    throw new Exception('invalid_entity_name', EQ_ERROR_INVALID_CONFIG);
}

// 2) path resolution

$parts = explode("\\", $entity);
$filename = array_pop($parts) . '.json';
$subdir = implode('/', $parts);

$lang_base_dir = "{$package_dir}/i18n/{$lang}";
$target_dir   = "{$lang_base_dir}/{$subdir}";

// retrieve lang folder
if(!is_dir($lang_base_dir)) {
    if(!$create) {
        throw new Exception('lang_not_found', EQ_ERROR_INVALID_CONFIG);
    }
    if(!mkdir($lang_base_dir, 0775, true)) {
        throw new Exception('cannot_create_lang_directory', EQ_ERROR_INVALID_CONFIG);
    }
}

// retrieve entity folder
if(!is_dir($target_dir) && !mkdir($target_dir, 0775, true)) {
    throw new Exception('cannot_create_entity_directory', EQ_ERROR_INVALID_CONFIG);
}

// validate received JSON & store new data
$data = json_decode($payload, true);

if(json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('payload_not_valid_json', EQ_ERROR_INVALID_CONFIG);
}

$output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if($output === false) {
    throw new Exception('json_encoding_failed', EQ_ERROR_INVALID_CONFIG);
}

// 3) write back resulting file
$target_file = "{$target_dir}/{$filename}";
if (file_put_contents($target_file, $output) === false) {
    throw new Exception('cannot_write_translation_file', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
    ->body($output)
    ->send();
