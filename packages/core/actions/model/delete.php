<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Deletes the given object(s).",
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
        ],
        'permanent' => [
            'type'          => 'boolean',
            'description'   => 'Flag for choosing either soft deletion (recycle bin) or hard deletion (removed from DB).',
            'default'       => false
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
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

if( empty($params['ids']) ) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", EQ_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

if(!class_exists($params['entity'])) {
    throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
}

$params['entity']::ids($params['ids'])->delete($params['permanent']);

// respond with a code 205 "Reset content" so that if controller was called by a view, it allows UI to close it
$context
    ->httpResponse()
    ->status(205)
    ->send();
