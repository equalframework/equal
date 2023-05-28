<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Deletes the given object(s).",
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
        ],
        'permanent' => [
            'description '  => 'Flag for choosing either soft deletion (recycle bin) or hard deletion (removed from DB).',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

if( empty($params['ids']) ) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", QN_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

if(!class_exists($params['entity'])) {
    throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
}

$params['entity']::ids($params['ids'])->delete($params['permanent']);

$context->httpResponse()
        ->status(204)
        ->send();