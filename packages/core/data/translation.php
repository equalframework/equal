<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Recursively retrieves the full map of translation values relating to a specified entity.",
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
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm']
]);

['context' => $context, 'orm' => $orm] = $providers;

$entity = $params['entity'];
$parents = [];

// for non-controller entities, retrieve parents hierarchy
$parts = explode('\\', $params['entity']);
$file = array_pop($parts);
if(!ctype_lower(substr($file, 0, 1))) {
    while(true) {
        $parent = get_parent_class($entity);
        if(!$parent) break;
        if($parent == 'equal\orm\Model') {
            // simulate Model class to be part of core package (to fallback to related translation files)
            $parents[] = 'core\Model';
        }
        else {
            $parents[] = $parent;
        }
        $entity = $parent;
    }
}

$parents[] = $params['entity'];

// init resulting lang schema
$result = [];

foreach($parents as $entity) {
    $parts = explode('\\', $entity);
    $package = array_shift($parts);

    $class_dir = implode('/', $parts);

    $files = [QN_BASEDIR."/packages/$package/i18n/{$params['lang']}/$class_dir.json"];
    if(strpos($params['lang'], '_') !== false) {
        // fallback on language only
        $language = explode('_', $params['lang'])[0];
        array_unshift($files, QN_BASEDIR."/packages/$package/i18n/$language/$class_dir.json");
    }

    foreach($files as $file) {
        if(!file_exists($file)) {
            continue;
        }

        if(($schema = json_decode(@file_get_contents($file), true)) === null) {
            throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
        }

        if(empty($result)) {
            $result = $schema;
        }
        else {
            $result = array_replace_recursive($result, $schema);
        }
    }
}

$context->httpResponse()
        ->body($result)
        ->send();
