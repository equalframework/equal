<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use equal\orm\Field;
use equal\orm\ObjectManager;
use equal\php\Context;
use equal\data\adapt\DataAdapterProvider;
use equal\error\Reporter;

[$params, $providers] = eQual::announce([
    'description'   => 'Import eQual objects from a given data payload (export-formatted).',
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
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm', 'adapt', 'report']
]);

/**
 * @var Context             $context
 * @var ObjectManager       $orm
 * @var DataAdapterProvider $dap
 * @var Reporter            $reporter
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap, 'report' => $reporter] = $providers;

$adapter = $dap->get('json');

/**
 * Methods
 */

$extractEntityAndSchema = function(array $params) use ($orm): array {
    $entity = $params['entity'] ?? $params['data']['name'] ?? null;
    if(is_null($entity)) {
        throw new Exception('missing_entity', EQ_ERROR_INVALID_PARAM);
    }

    $model = $orm->getModel($entity);
    if(!$model) {
        throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
    }

    return [$entity, $model->getSchema()];
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

$extractLang = function(array $params) {
    if(isset($params['lang'])) {
        return $params['lang'] ?? constant('DEFAULT_LANG');
    }

    return $params['data']['lang'] ?? constant('DEFAULT_LANG');
};

/**
 * Action
 */

[$entity, $schema] = $extractEntityAndSchema($params);
$data = $extractData($params);
$lang = $extractLang($params);

$object_ids = [];
foreach($data as $entity_data) {
    foreach($entity_data as $field => $value) {
        if(!isset($schema[$field])) {
            $reporter->warning("ORM::unknown field $field for entity $entity in given data.");
            continue;
        }
        $f = new Field($schema[$field]);
        $entity_data[$field] = $adapter->adaptIn($value, $f->getUsage());
    }

    if($entity === 'core\setting\Setting') {
        // references to a Setting should not rely on its `id`
        if(isset($entity_data['id'])) {
            unset($entity_data['id']);
        }
    }
    elseif($entity === 'core\setting\SettingValue') {
        // `id` is discarded to prevent mixing up references across settings from several packages
        if(isset($entity_data['id'])) {
            unset($entity_data['id']);
        }
        if(!isset($entity_data['name'])) {
            throw new Exception('missing_setting_name', EQ_ERROR_INVALID_CONFIG);
        }
        $settings_ids = Setting::search(['name', '=', $entity_data['name']])->ids();
        if(!count($settings_ids)) {
            throw new Exception('unknown_setting', EQ_ERROR_INVALID_CONFIG);
        }
        $entity_data['setting_id'] = reset($settings_ids);
    }

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

    $object_ids[] = $id;
}

$computed_fields = [];
foreach($schema as $field => $def) {
    if($def['type'] === 'computed') {
        $computed_fields[] = $field;
    }
}

if(!empty($computed_fields)) {
    $orm->read($entity, $object_ids, $computed_fields, $lang);
}

$result = array_map(
    function($id) {
        return ['id' => $id];
    },
    $object_ids
);

$context->httpResponse()
        ->body($result)
        ->status(200)
        ->send();
