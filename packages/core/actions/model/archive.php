<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "mark the given object(s) as archived.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'id' =>  [
            'description'   => 'Unique identifier of the object to remove.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'ids' =>  [
            'description'   => 'List of Unique identifiers of the objects to update.',
            'type'          => 'array',
            'default'       => []
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'access'] 
]);


list($context, $orm, $ac) = [$providers['context'], $providers['orm'], $providers['access']];

if( empty($params['ids']) ) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", QN_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

if(!class_exists($params['entity'])) {
    throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
}

if(!$ac->isAllowed(QN_R_WRITE, $params['entity'], ['state'], $params['ids'])) {
    throw new Exception('UPDATE;'.$params['entity'].';['.implode(',', $fields).'];['.implode(',', $ids).']', QN_ERROR_NOT_ALLOWED);
}

$res = $orm->write($params['entity'], $params['ids'], ['state' => 'archived']);
if($res <= 0) {
    throw new Exception($params['entity'].'::state', $res);
}

$context->httpResponse()
        ->status(204)
        ->body([])
        ->send();