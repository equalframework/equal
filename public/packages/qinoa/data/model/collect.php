<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\orm\Domain;

list($params, $providers) = announce([
    'description'   => 'Returns a list of entites according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Requested fields. If not specified, only \'id\' and \'name\' fields are returned.',
            'type'          => 'array', 
            'default'       => ['id', 'name']
        ],
        'lang' =>  [
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
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
            'min'           => 1,
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

$domain = $params['domain'];

// if `deleted` field is requested, we need to force searching amongst deleted objects as well
if(in_array('deleted', $params['fields'])) {
    $domain = Domain::conditionAdd($domain, ['deleted', 'in', [0, 1]]);
}

$collection = $params['entity']::search($domain, [ 'sort' => [ $params['order'] => $params['sort']] ]);

$total = count($collection->ids());

// retrieve list
$result = $collection
          ->shift($params['start'])
          ->limit($params['limit'])
          ->read($params['fields'])
          ->adapt('txt')
          // return result as an array (since JSON objects handled by ES2015+ might have their keys order altered)
          ->get(true);


$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();