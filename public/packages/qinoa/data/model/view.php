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
            'description'   => 'The identifier of the view (list.default, form.kanban).',
            'type'          => 'string', 
            'required'      => true
        ],
        
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'        
    ],
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [ $providers['context'], $providers['orm'] ];

$package = $orm->getObjectPackageName($params['entity']);
$class = $orm->getObjectName($params['entity']);

$class_dir = str_replace('\\', '/', $class);

$file = QN_BASEDIR."/public/packages/{$package}/views/{$class_dir}.{$params['view_id']}.json";

if(!file_exists($file)) {
    throw new Exception("unknown_view", QN_ERROR_UNKNOWN_OBJECT);
}

if( ($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($view)
        ->send();