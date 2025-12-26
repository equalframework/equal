<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'description'   => "Returns the MD formatted help relating to a given entity (class model), given a view ID (<type.name>).",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'list'
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

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

// fallback to empty string
$result = '';

[$view_type, $view_name] = explode('.', $params['view_id']);

$entity = str_replace('_', '\\', $params['entity']);

$file = null;

// retrieve existing view meant for entity or it
while(true) {
    $parts = explode('\\', $entity);
    $package = array_shift($parts);
    $filename = array_pop($parts);
    $class_path = implode('/', $parts);

    $files = [
        EQ_BASEDIR . "/packages/{$package}/i18n/{$params['lang']}/{$class_path}/{$filename}.{$view_type}.{$view_name}.md"
    ];
    if(strpos($params['lang'], '_') !== false) {
        // fallback on language only
        $language = explode('_', $params['lang'])[0];
        $files[] = EQ_BASEDIR . "/packages/{$package}/i18n/{$language}/{$class_path}/{$filename}.{$view_type}.{$view_name}.md";
    }
    $files[] = EQ_BASEDIR . "/packages/{$package}/views/{$class_path}/{$filename}.{$view_type}.{$view_name}.md";

    foreach($files as $f) {
        if(file_exists($f)) {
            $file = $f;
            break 2;
        }
    }

    // go one level up through parents
    try {
        if(ctype_lower(substr($filename, 0, 1))) {
            // support for controller entities
            break;
        }
        $parent = get_parent_class($orm->getModel($entity));
        if(!$parent || $parent == 'equal\orm\Model') {
            break;
        }
        $entity = $parent;
    }
    catch(Throwable $e) {
        // unexpected error (unknown class)
        break;
    }
}

if(!is_null($file) && file_exists($file)) {
    if(($result = @file_get_contents($file)) === null) {
        throw new Exception("unable_to_read_file", EQ_ERROR_INVALID_CONFIG);
    }
}

$context->httpResponse()
        ->body(['result' => $result])
        ->send();
