<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => "Retrieve the list of all objects of a given model, and output it as a JSON collection suitable for import.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to use (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'lang' =>  [
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


list($context, $orm, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

$entity = $params['entity'];

$model = $orm->getModel($entity);

if(!$model) {
    throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
}

$schema = $model->getSchema();

$fields = [];

foreach($schema as $field => $descr) {
    if($descr['type'] != 'one2many' && $descr['type'] != 'computed') {
        $fields[] = $field;
    }
}


$output = [];

$series = [
    "name" => $entity,
    "lang" => $params['lang'],
    "data" => []
];

$values = $params['entity']::search()
    ->read($fields)
    ->adapt('json')
    ->get(true);

foreach($values as $odata) {
    $series["data"][] = $odata;
}

$output[] = $series;

$context->httpResponse()
        ->body($output)
        ->send();