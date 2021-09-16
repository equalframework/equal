<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Returns the JSON view related to an entity (class model), given a view ID (<type.name>).",
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
    $file = QN_BASEDIR."/packages/{$package}/views/{$class_path}.{$params['view_id']}.json";
    if(file_exists($file)) break;
    if(!$parent || $parent == 'equal\orm\Model') break;
    $entity = $parent;
}

if(!file_exists($file)) {
    throw new Exception("unknown_view_id", QN_ERROR_UNKNOWN_OBJECT);
}

if( ($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($view)
        ->send();