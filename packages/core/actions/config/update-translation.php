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
            'type' => 'string',
            'required' => true
        ],
        'entity' => [
            'type' => 'string',
            'required' => true
        ],
        'lang' => [
            'type' => 'string',
            'required' => true
        ],
        'payload' => [
            'type' => 'string',
            'usage' => 'text/json',
            'default' => '{}'
        ],
        'create_lang' => [
            'type' => 'boolean',
            'default' => false
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

/* ---------- Validations ---------- */

// Package
$packageDir = EQ_BASEDIR . "/packages/{$package}";
if (!is_dir($packageDir)) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}

// Lang format (ex: fr, fr_BE, en_US)
if (!preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $lang)) {
    throw new Exception('invalid_lang_format', EQ_ERROR_INVALID_CONFIG);
}

// Entity safety
if (!preg_match('/^[A-Za-z0-9_\\\\]+$/', $entity)) {
    throw new Exception('invalid_entity_name', EQ_ERROR_INVALID_CONFIG);
}

/* ---------- Path resolution ---------- */

$parts = explode("\\", $entity);
$filename = array_pop($parts) . '.json';
$subdir = implode('/', $parts);

$langBaseDir = "{$packageDir}/i18n/{$lang}";
$targetDir   = "{$langBaseDir}/{$subdir}";

// Lang folder
if (!is_dir($langBaseDir)) {
    if (!$create) {
        throw new Exception('lang_not_found', EQ_ERROR_INVALID_CONFIG);
    }
    if (!mkdir($langBaseDir, 0775, true)) {
        throw new Exception('cannot_create_lang_directory', EQ_ERROR_INVALID_CONFIG);
    }
}

// Entity folder
if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
    throw new Exception('cannot_create_entity_directory', EQ_ERROR_INVALID_CONFIG);
}

/* ---------- JSON handling ---------- */

$data = json_decode($payload, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('payload_not_valid_json', EQ_ERROR_INVALID_CONFIG);
}

$output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($output === false) {
    throw new Exception('json_encoding_failed', EQ_ERROR_INVALID_CONFIG);
}

/* ---------- Write ---------- */

$targetFile = "{$targetDir}/{$filename}";
if (file_put_contents($targetFile, $output) === false) {
    throw new Exception('cannot_write_translation_file', EQ_ERROR_INVALID_CONFIG);
}

/* ---------- Response ---------- */

$context->httpResponse()
    ->status(200)
    ->body($output)
    ->send();
