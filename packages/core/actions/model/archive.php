<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "mark the given object(s) as archived.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'required'      => true
        ],
        'id' => [
            'type'          => 'integer',
            'description'   => 'Unique identifier of the object to remove.',
            'default'       => 0
        ],
        'ids' => [
            'type'          => 'array',
            'description'   => 'List of Unique identifiers of the objects to update.',
            'default'       => []
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\access\AccessController  $ac
 */
['context' => $context, 'orm' => $orm, 'access' => $ac] = $providers;

if( empty($params['ids']) ) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", EQ_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

if(!class_exists($params['entity'])) {
    throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
}

if(!$ac->isAllowed(EQ_R_WRITE, $params['entity'], ['state'], $params['ids'])) {
    throw new Exception('UPDATE;'.$params['entity'].';['.implode(',', $fields).'];['.implode(',', $ids).']', EQ_ERROR_NOT_ALLOWED);
}

$res = $orm->update($params['entity'], $params['ids'], ['state' => 'archive']);
if($res <= 0) {
    throw new Exception($params['entity'].'::state', $res);
}

$context
    ->httpResponse()
    ->status(204)
    ->send();