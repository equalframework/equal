<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;
use equal\orm\Field;

list($params, $providers) = announce([
    'description'   => 'Updates a list of entities matching a given domain (filter). This is the symmetrical operation of `model_collect`.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Associative array mapping fields to be updated with theirs related values to be assigned.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' =>  [
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'description'   => 'Criterias that results have to match (series of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'orm', 'adapt' ]
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $orm, $dap) = [ $providers['context'], $providers['orm'], $providers['adapt'] ];

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');


// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

// get the complete schema of the object (including special fields)
$schema = $entity->getSchema();

// adapt received fields names for dot notation support
$fields = [];
foreach($params['fields'] as $field => $value) {
    if(!isset($schema[$field])) {
        continue;
    }
    $f = new Field($schema[$field]);
    $fields[$field] = $adapter->adaptIn($value, $f->getUsage());
}

$domain = $params['domain'];

// if domain contains a condition that targets `id` field, force searching regardless the state (this is the case for form views)
$has_id_clause = false;
foreach($domain as $clause) {
    if(is_array($clause)) {
        foreach($clause as $condition) {
            if(is_array($condition)) {
                $has_id_clause = ($condition[0] == 'id');
            }
            else {
                $has_id_clause = ($condition == 'id');
            }
        }
    }
    else {
        $has_id_clause = ($clause == 'id');
    }
}
if($has_id_clause) {
    $domain = Domain::conditionAdd($domain, ['state', '<>', 'unknown']);
}

$collection = $params['entity']::search($domain);

// update list
$collection->update($fields, $params['lang']);

$context->httpResponse()
        ->status(204)
        ->send();
