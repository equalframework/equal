<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Retrieve the list of all objects of a given model, and output it as a JSON collection suitable for import.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to use (e.g. \'core\\User\').',
            'required'      => true
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Language in which labels and multilang field have to be returned (2 letters ISO 639-1).',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'type'          => 'array',
            'description'   => 'Criterias that results have to match (series of conjunctions)',
            'default'       => []
        ],
        'system' => [
            'type'          => 'boolean',
            'description'   => 'Include values of system fields in the export (`creator`, `created`, `state`, ...).',
            'default'       => false
        ],
        'relations' => [
            'type'          => 'string',
            'description'   => 'Kind of relational fields to consider for export.',
            'selection'     => [
                'none',
                'many2one',
                'one2many',
                'many2many'
            ],
            'default'       => 'many2one'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

$entity = $params['entity'];

$model = $orm->getModel($entity);

if(!$model) {
    throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
}

$fields = [];

$schema = $model->getSchema();

$accepted_relational_types = [
        'many2many' => ['many2one', 'one2many', 'many2many'],
        'one2many'  => ['many2one', 'one2many'],
        'many2one'  => ['many2one'],
        'none'      => []
    ][$params['relations']];

foreach($schema as $field => $descr) {
    if($descr['type'] == 'alias') {
        continue;
    }
    $f = $model->getField($field);
    $descriptor = $f->getDescriptor();
    $type = $descriptor['result_type'];
    if(in_array($type, ['many2one', 'one2many', 'many2many'])) {
        if(!in_array($type, $accepted_relational_types)) {
            continue;
        }
    }
    $fields[] = $field;
}

$output = [];

$series = [
    "name" => $entity,
    "lang" => $params['lang'],
    "data" => []
];

$values = $params['entity']::search($params['domain'])
    ->read($fields)
    ->adapt('json')
    ->get(true);

foreach($values as $object) {
    if(!$params['system']) {
        array_walk($object, function($value, $field) use(&$object) {
                if(in_array($field, ['creator', 'modifier', 'created', 'modified', 'state', 'deleted'])) {
                    unset($object[$field]);
                }
            });
    }
    $series["data"][] = $object;
}

// #todo - add support for series with multilang translations

$output[] = $series;

$context
    ->httpResponse()
    ->body($output)
    ->send();
