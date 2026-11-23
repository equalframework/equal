<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;
use equal\orm\DomainCondition;
use equal\orm\Field;

[$params, $providers] = eQual::announce([
    'description'   => 'Updates a list of entities matching a given domain (filter). This is the symmetrical operation of `model_collect`.',
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'required'      => true
        ],
        'fields' => [
            'type'          => 'array',
            'description'   => 'Associative array mapping fields to be updated with theirs related values to be assigned.',
            'default'       => []
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'type'          => 'array',
            'description'   => 'Criterias that results have to match (series of conjunctions)',
            'default'       => []
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
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => [ 'context', 'orm', 'adapt' ]
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\data\DataAdapterProvider $dap
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap] = $providers;

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

// retrieve target entity
$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

// get the complete schema of the object (including special fields)
$schema = $model->getSchema();

// adapt received fields names for dot notation support
$fields = [];
foreach($params['fields'] as $field => $value) {
    if(!isset($schema[$field])) {
        continue;
    }
    /** @var equal\orm\Field */
    $f = $model->getField($field);
    $fields[$field] = $adapter->adaptIn($value, $f->getUsage());
}

$domain = new Domain($params['domain']);

// if domain contains a condition that targets `id` field, force searching regardless the state (this is the case for form views)
$has_id_clause = false;
foreach($domain->getClauses() as $clause) {
    foreach($clause->getConditions() as $condition) {
        if($condition->getOperand() === 'id' && in_array($condition->getOperator(), ['=', 'in'], true)) {
            $has_id_clause = true;
            break 2;
        }
    }
}

if($has_id_clause) {
    $domain->addCondition(new DomainCondition('state', '<>', 'unknown'));
}

$collection = $params['entity']::search($domain->toArray());

// update list
$collection->update($fields, $params['lang']);

$context
    ->httpResponse()
    ->status(204)
    ->send();
