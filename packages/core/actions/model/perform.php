<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to perform an action on an object or a collection of a given class.",
    'help'          => "Deprecated:  use core_model_do instead",
    'deprecated'    => true,
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
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
        ],
        'action' => [
            'type'          => 'string',
            'description'   => 'Name of the action to attempt to perform on the objects.',
            'required'      => true
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
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

// sanitize ids
if(empty($params['ids'])) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("missing_id", EQ_ERROR_MISSING_PARAM);
    }
    $params['ids'][] = $params['id'];
}

$entity::ids($params['ids'])->do($params['action']);

$context
    ->httpResponse()
    ->status(204)
    ->send();
