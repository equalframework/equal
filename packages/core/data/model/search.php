<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns a list of identifiers of a given entity, according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'required'      => true
        ],
        'domain' => [
            'type'          => 'array',
            'description'   => 'Criterias that results have to match (serie of conjunctions)',
            'default'       => []
        ],
        'order' => [
            'type'          => 'string',
            'description'   => 'Column to use for sorting results.',
            'default'       => 'id'
        ],
        'sort' => [
            'type'          => 'string',
            'description'   => 'The direction  (i.e. \'asc\' or \'desc\').',
            'default'       => 'asc'
        ],
        'start' => [
            'type'          => 'integer',
            'description'   => 'The row from which results have to start.',
            'default'       => 0
        ],
        'limit' => [
            'type'          => 'integer',
            'description'   => 'The maximum number of results.',
            'default'       => -1
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*',
        'cacheable'     => false
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", EQ_ERROR_UNKNOWN_OBJECT);
}

$collection = $params['entity']::search($params['domain'], [ 'sort' => [ $params['order'] => $params['sort']] ]);

$total = count($collection->ids());

$result = $collection
          ->shift($params['start'])
          ->limit($params['limit'])
          ->ids();

$context
    ->httpResponse()
    ->header('X-Total-Count', $total)
    ->body($result)
    ->send();