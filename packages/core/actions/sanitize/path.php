<?php

list($params, $providers) = eQual::announce([
    'description' => 'This is the core_config_init-data controller created with core_config_create-controller.',
    'response' => [
        'accept-origin' => [
            0 => '*',
        ],
        'charset' => 'utf-8',
        'schema' => [
            'type' => 'string',
            'qty' => 'one',
            'usage' => '',
        ],
    ],
    'params' => [
        'path' => [
            'description' => 'path you want to sanitize',
            'help' => '',
            'type' => 'string',
            'required' => true,
        ],
        'name_only' => [
            'description' => 'delete all slashes',
            'help' => '',
            'type' => 'boolean',
            'default' => 'false',
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
$banned = [
    "%2e%2e%2f ",
    "../",
    "%2e%2e/ ",
    "../",
    "..%2f ",
    "../",
    "%2e%2e%5c ",
    "..\\",
    "%2e%2e\\",
    "..\\",
    "..%5c ",
    "..\\",
    "%252e%252e%255c",
    "..\\",
    "..%255c",
    "..\\",
    "\0"
];

$path = $params['path'];
$sanitized = $params['path'];


foreach ($banned as $banned_item) {
    $sanitized = str_replace($banned_item,"",$sanitized);
}

if($params['name_only']) {
    $sanitized = str_replace("/","",$sanitized);
    $sanitized = str_replace("\\","",$sanitized);
}


// controller logic goes here
$context->httpResponse()
    ->body($sanitized)
    ->status(200)
    ->send();
