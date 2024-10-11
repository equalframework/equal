<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
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

try {
    $entity = $params['entity'];

    $model = $orm->getModel($entity);

    if(!$model) {
        throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
    }

    // retrieve existing view meant for entity or it
    while(true) {
        $parts = explode('\\', $entity);
        $package = array_shift($parts);
        $class_path = implode('/', $parts);
        $parent = get_parent_class($entity);

        $file = QN_BASEDIR."/packages/{$package}/i18n/{$params['lang']}/{$class_path}.{$params['view_id']}.md";
        if(!file_exists($file)) {
            $file = QN_BASEDIR."/packages/{$package}/views/{$class_path}.{$params['view_id']}.md";
        }

        if(file_exists($file)) {
            break;
        }

        if(!$parent || $parent == 'equal\orm\Model') {
            break;
        }
        $entity = $parent;
    }

    if(file_exists($file)) {
        if( ($result = @file_get_contents($file)) === null) {
            throw new Exception("unable_to_read_file", QN_ERROR_INVALID_CONFIG);
        }
    }

}
catch(Exception $e) {
    // #memo - unless an unexpected I/O error, this script should always returns a value
    // throw new Exception("unknown_view_id", QN_ERROR_UNKNOWN_OBJECT);
}

$context->httpResponse()
        ->body(['result' => $result])
        ->send();
