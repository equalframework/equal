<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
// #deprecated - use `core_config_translation` instead
list($params, $providers) = eQual::announce([
    'description'   => "Retrieves the translation values related to the specified entity.",
    'deprecated'    => true,
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
$lang = [];

foreach($parents as $entity) {
    $parts = explode('\\', $entity);
    $package = array_shift($parts);

    $class_dir = implode('/', $parts);
    $file = EQ_BASEDIR."/packages/{$package}/i18n/{$params['lang']}/{$class_dir}.json";

    if(!file_exists($file)) {
        continue;
    }

    if(($schema = json_decode(@file_get_contents($file), true)) === null) {
        throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
    }
    if(empty($lang)) {
        $lang = $schema;
    }
    else {
        $lang = array_replace_recursive($lang, $schema);
    }
}

/*
if(empty($lang)) {
    throw new Exception("unknown_lang_file", QN_ERROR_UNKNOWN_OBJECT);
}
*/

$context->httpResponse()
        ->body($lang)
        ->send();
