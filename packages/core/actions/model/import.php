<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\ObjectManager;
use equal\php\Context;

[$params, $providers] = eQual::announce([
    'description'   => 'Import eQual object from import format data.',
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'usage'         => 'orm/entity',
            'description'   => 'Full name (including namespace) of the class to import (e.g. "core\\User").'
        ],
        'data' => [
            'type'          => 'array',
            'description'   => 'List of objects to import.',
            'help'          => 'If the entity parameter is not provided, this parameter must match eQual export format (e.g. "{\"entity\":\"core\\\\User\",\"data\":[...]}").',
            'required'      => true
        ],
        'lang' => [
            'type'          => 'string',
            'description '  => 'Specific language for multilang field.',
            'help'          => 'If not provided the DEFAULT_LANG is used.',
            'default'       => constant('DEFAULT_LANG')
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
 * @var Context         $context
 * @var ObjectManager   $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

/**
 * Methods
 */

$extractData = function(array $params) {
    $data = $params['data']['data'] ?? $params['data'] ?? null;
    if(is_null($data)) {
        throw new Exception('missing_data', EQ_ERROR_INVALID_PARAM);
    }

    return $data;
};

$extractEntity = function($params) use ($orm) {
    $entity = $params['data']['name'] ?? $params['entity'] ?? null;
    if(is_null($entity)) {
        throw new Exception('missing_entity', EQ_ERROR_INVALID_PARAM);
    }

    $model = $orm->getModel($entity);
    if(!$model) {
        throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
    }

    return $entity;
};

$extractLang = function($params) {
    return $params['data']['lang'] ?? $params['lang'];
};

/**
 * Action
 */

$data = $extractData($params);
$entity = $extractEntity($params);
$lang = $extractLang($params);

foreach($data as $entity_data) {
    $entity_exists = false;
    if(isset($entity_data['id'])) {
        $ids = $entity::id($entity_data['id'])->ids();
        $entity_exists = !empty($ids);
    }

    if($entity_exists) {
        $entity::id($entity_data['id'])
            ->update($entity_data, $lang);
    }
    else {
        $entity::create($entity_data, $lang);
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
