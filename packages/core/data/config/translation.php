<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
// #deprecated - use `core_translation` instead
list($params, $providers) = eQual::announce([
    'description'   => "Retrieves the translation values related to the specified entity without checking for parent traductions.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look for (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'lang' =>  [
            'description'   => 'Language for which values are requested (iso639 code expected).',
            'type'          => 'string',
            'default' 		=> constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm']
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];

$entity = $params['entity'];

// init resulting lang schema
$lang = [];

$parts = explode('\\', $entity);
$package = array_shift($parts);

$class_dir = implode('/', $parts);

$files = [QN_BASEDIR."/packages/$package/i18n/{$params['lang']}/$class_dir.json"];
if(strpos($params['lang'], '_') !== false) {
    // fallback on language only
    $language = explode('_', $params['lang'])[0];
    $files[] = QN_BASEDIR."/packages/$package/i18n/$language/$class_dir.json";
}

$file = null;
foreach($files as $f) {
    if(file_exists($f)) {
        $file = $f;
        break;
    }
}

if(is_null($file)) {
    throw new Exception("unknown_lang_file", QN_ERROR_UNKNOWN_OBJECT);
}

if(($schema = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
}



$context->httpResponse()
        ->body($schema)
        ->send();