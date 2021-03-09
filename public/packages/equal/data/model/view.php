<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => "Returns the view of given class (model) under given context.",
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

$file = QN_BASEDIR."/public/packages/{$package}/views/{$class}.{$params['view_id']}.json";

if(!file_exists($file)) {
    throw new Exception("unknown_view_id", QN_ERROR_UNKNOWN_OBJECT);
}


if( ($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($view)
        ->send();