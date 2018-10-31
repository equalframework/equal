<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => "Returns the view of given class (model) under given context.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'context' =>  [
            'description'   => 'The context of the view (list, show, create, edit, delete).',
            'type'          => 'string', 
            'default'       => 'list'
        ],
        
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'        
    ],
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [$providers['context'], $providers['orm']];

$package = $orm->getObjectPackageName($params['entity']);
$class = $orm->getObjectName($params['entity']);

$file = QN_BASE_DIR."/public/packages/{$package}/views/{$class}.{$params['context']}.json";

if(!file_exists($file)) {
// todo : auto-generate a default view

// load schema
// keep 5 first (non-relational) fields + default label
    throw new Exception("no view defined for '{$params['entity']}' in '{$params['context']}' context", QN_ERROR_UNKNOWN_OBJECT);
}


if( ($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed json in view config file $file", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($view)
        ->send();