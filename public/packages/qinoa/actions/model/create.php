<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => "Create a new object using given fields values.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Associative map of fields to be updated, with their related values.',
            'type'          => 'array', 
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);


list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

// fields and values have been received as a raw array : some additional treatment might be required
$schema = $orm->getModel($params['entity'])->getSchema();

foreach($params['fields'] as $field => $value) {
    // drop empty fields
    if(is_null($value)) {
        unset($params['fields'][$field]);
    }
    else {
        $params['fields'][$field] = $adapter->adapt($value, $schema[$field]['type']);
    }
}

$instance = $params['entity']::create($params['fields'], $params['lang'])->adapt('txt')->first();

$result = ['entity'  => $params['entity'], 'id' => $instance['id']];

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();