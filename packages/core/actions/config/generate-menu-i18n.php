<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Creates a new i18n translation file for a menu.",
    'params'        => [

        'package' => [
            'type'          => 'string',
            'description'   => "Name of the package.",
            'required'      => true
        ],

        'menu_name' => [
            'type'          => 'string',
            'description'   => "Name of the menu.",
            'required'      => true
        ],

        'lang' => [
            'type'          => 'string',
            'description'   => "Code of the desired lang.",
            'required'      => true
        ],

        'overwrite' => [
            'type'          => 'boolean',
            'description'   => "If true the file will be overwritten if it already exists.",
            'default'       => false
        ],

        'payload' => [
            'type'          => 'array',
            'description'   => "JSON structure containing the menu view items.",
            'required'      => false
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
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context          $context
 */
['context' => $context] = $providers;

$package = $params['package'];

if(!file_exists("packages/$package")) {
    throw new Exception("unknown_package", EQ_ERROR_INVALID_CONFIG);
}

if(!file_exists("packages/$package/i18n")) {
    throw new Exception("missing_i18n_dir", EQ_ERROR_INVALID_CONFIG);
}

$lang = $params['lang'];
if(strpos($lang, '_') === false) {
    if(strlen($lang) !== 2) {
        throw new Exception("invalid_lang", EQ_ERROR_INVALID_PARAM);
    }
}
else {
    // lang can be a locale (e.g., fr_BE)
    $lang_parts = explode('_', $lang);
    if(count($lang_parts) !== 2 || strlen($lang_parts[0]) !== 2) {
        throw new Exception("invalid_lang", EQ_ERROR_INVALID_PARAM);
    }
    if(strlen($lang_parts[1]) !== 2) {
        throw new Exception("invalid_country", EQ_ERROR_INVALID_PARAM);
    }
}

$file_name = "menu.{$params['menu_name']}";
$file = EQ_BASEDIR."/packages/$package/i18n/$lang/$file_name.json";

// prevent overwriting existing file
if(file_exists($file) && !$params['overwrite']) {
    throw new Exception("i18n_already_exists", EQ_ERROR_INVALID_PARAM);
}

if(!is_dir(EQ_BASEDIR."/packages/$package/i18n/$lang")) {
    mkdir(EQ_BASEDIR."/packages/$package/i18n/$lang", 0777, true);
    if(!is_dir(EQ_BASEDIR."/packages/$package/i18n/$lang")) {
        throw new Exception("file_access_denied", EQ_ERROR_UNKNOWN);
    }
}

$result = [
    'description'   => '',
    'view'          => []
];

// Merge payload if provided
if(!empty($params['payload']) && is_array($params['payload'])) {
    $result = array_merge($result, $params['payload']);
}

if(!file_put_contents($file, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))) {
    throw new Exception("file_access_denied", EQ_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
