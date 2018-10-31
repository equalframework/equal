<?php
/*
    This file is part of the Resipedia project <http://www.github.com/cedricfrancoys/resipedia>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => 'Returns a list of entites according to given domain (filter), start offset, limit and order.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],    
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
            'default'       => 'desc'
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
    'providers'     => ['context'] 
]);

list($context) = [ $providers['context'] ];

if(!class_exists($params['entity'])) {
    throw new Exception("unknown class '{$params['entity']}'", QN_ERROR_UNKNOWN_OBJECT);
}

$collection = $params['entity']::search($params['domain'], [ 'sort' => [ $params['order'] => $params['sort']] ]);

$total = count($collection->ids());

// retrieve list
$result = $collection
          ->shift($params['start'])
          ->limit($params['limit'])
          ->read($params['fields'])
          ->adapt('txt')
          ->get(true);


$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();