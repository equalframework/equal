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
            'help'          => 'If not provided the DEFAULT_LANG is used.'
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

$extractEntity = function($params) use ($orm) {
    $entity = $params['entity'] ?? $params['data']['name'] ?? null;
    if(is_null($entity)) {
        throw new Exception('missing_entity', EQ_ERROR_INVALID_PARAM);
    }

    $model = $orm->getModel($entity);
    if(!$model) {
        throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
    }

    return $entity;
};

$extractData = function(array $params) {
    if(isset($params['entity'])) {
        $data = $params['data'];
    }
    else {
        $data = $params['data']['data'] ?? null;
    }

    if(is_null($data)) {
        throw new Exception('missing_data', EQ_ERROR_INVALID_PARAM);
    }

    return $data;
};

$extractLang = function($params) {
    if(isset($params['entity'])) {
        return $params['lang'] ?? constant('DEFAULT_LANG');
    }

    return $params['data']['lang'] ?? constant('DEFAULT_LANG');
};

/**
 * Action
 */

$entity = $extractEntity($params);
$data = $extractData($params);
$lang = $extractLang($params);

$result = [];
foreach($data as $entity_data) {
    if(isset($entity_data['id'])) {
        $ids = $orm->search($entity, ['id', '=', $entity_data['id']]);
        if($ids < 0 || !count($ids)) {
            $orm->create($entity, ['id' => $entity_data['id']], $lang, false);
        }

        $id = $entity_data['id'];
        unset($entity_data['id']);
    }
    else {
        $id = $orm->create($entity, [], $lang);
    }

    $orm->update($entity, $id, $entity_data, $lang);

    $result[] = ['id' => $id];
}

$context->httpResponse()
        ->body($result)
        ->status(200)
        ->send();
