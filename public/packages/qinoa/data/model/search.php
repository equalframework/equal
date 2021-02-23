<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => 'Returns a list of identifiers of a given entity, according to given domain (filter), start offset, limit and order.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*',
        'cacheable'         => false
    ],    
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'domain' => [
            'description'   => 'Criterias that results have to match (serie of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ],
        'order' => [
            'description'   => 'Column to use for sorting results.',
            'type'          => 'string',
            'default'       => 'id'
        ],
        'sort' => [
            'description'   => 'The direction  (i.e. \'asc\' or \'desc\').',
            'type'          => 'string',
            'default'       => 'asc'
        ],
        'start' => [
            'description'   => 'The row from which results have to start.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'limit' => [
            'description'   => 'The maximum number of results.',
            'type'          => 'integer',
            'min'           => 5,
            'max'           => 100,
            'default'       => 25
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ] 
]);

list($context) = [ $providers['context'] ];

if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
}

$collection = $params['entity']::search($params['domain'], [ 'sort' => [ $params['order'] => $params['sort']] ]);

$total = count($collection->ids());

$result = $collection
          ->shift($params['start'])
          ->limit($params['limit'])
          ->ids();

$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();