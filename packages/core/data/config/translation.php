<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

// #deprecated - use `core_translation` instead
[$params, $providers] = eQual::announce([
    'description'   => "Retrieves the translation values related to the specified entity without checking for parent traductions.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to look for (e.g. \'core\\User\').',
            'required'      => true
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Language for which values are requested (iso639 code expected).',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$entity = $params['entity'];

$parts = explode('\\', $entity);
$package = array_shift($parts);

$class_dir = implode('/', $parts);

$files = [EQ_BASEDIR."/packages/$package/i18n/{$params['lang']}/$class_dir.json"];
if(strpos($params['lang'], '_') !== false) {
    // fallback on language only
    $language = explode('_', $params['lang'])[0];
    $files[] = EQ_BASEDIR."/packages/$package/i18n/$language/$class_dir.json";
}

$file = null;
foreach($files as $f) {
    if(file_exists($f)) {
        $file = $f;
        break;
    }
}

if(is_null($file)) {
    throw new Exception("unknown_lang_file", EQ_ERROR_UNKNOWN_OBJECT);
}

if(($schema = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_json", EQ_ERROR_INVALID_CONFIG);
}

$context
    ->httpResponse()
    ->body($schema)
    ->send();