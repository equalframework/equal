<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\import\DataTransformer;
use core\import\EntityMapping;
use equal\data\adapt\DataAdapterProvider;
use equal\orm\ObjectManager;
use equal\php\Context;

[$params, $providers] = eQual::announce([
    'description'   => 'Import eQual model data from external source using EntityMapping.',
    'params'        => [
        'entity_mapping_id' => [
            'type'          => 'integer',
            'description'   => 'The ID of the entity mapping configuration to use.',
            'min'           => 1,
            'required'      => true
        ],
        'data' => [
            'type'          => 'array',
            'description'   => 'The data that needs to be imported.',
            'required'      => true
        ],
        'indexation' => [
            'type'          => 'array',
            'description'   => 'List of field keys that describe the indexation of origin data rows.',
            'help'          => 'Must be a list of strings. If empty the mapping is done on array index or key of associative array.'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var Context             $context
 * @var ObjectManager       $orm
 * @var DataAdapterProvider $dap
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap] = $providers;

$adapter = $dap->get('json');

/**
 * Methods
 */

$createMapColMapping = function(array $entity_mapping, array $data, array $indexation) {
    $map_col_mapping = [];

    switch($entity_mapping['mapping_type']) {
        case 'index':
            foreach(array_keys($data[0]) as $index) {
                $col_mapping_found = false;
                foreach($entity_mapping['column_mappings_ids'] as $col_mapping) {
                    if($col_mapping['origin_index'] === $index) {
                        $map_col_mapping[] = $col_mapping;
                        $col_mapping_found = true;
                        break;
                    }
                }
                if(!$col_mapping_found) {
                    $map_col_mapping[] = null;
                }
            }
            break;
        case 'name':
            if(!empty($indexation)) {
                $not_string_keys = array_filter(
                    $indexation,
                    function($key) {
                        return !is_string($key);
                    }
                );

                if(!empty($not_string_keys)) {
                    throw new Exception('indexation_must_be_an_array_of_strings', EQ_ERROR_INVALID_PARAM);
                }

                foreach($indexation as $column_key) {
                    foreach($entity_mapping['column_mappings_ids'] as $col_mapping) {
                        if($col_mapping['origin_name'] === $column_key) {
                            $map_col_mapping[] = $col_mapping;
                        }
                    }
                }
            }
            else {
                foreach(array_keys($data[0]) as $column_key) {
                    foreach($entity_mapping['column_mappings_ids'] as $col_mapping) {
                        if($col_mapping['origin_name'] === $column_key) {
                            $map_col_mapping[$column_key] = $col_mapping;
                        }
                    }
                }
            }
            break;
    }

    foreach($map_col_mapping as $col_mapping) {
        usort($col_mapping['data_transformers_ids'], 'order');
    }

    return $map_col_mapping;
};

/**
 * Action
 */

if(empty($params['data'][0])) {
    throw new Exception('empty_data', EQ_ERROR_INVALID_PARAM);
}

$entity_mapping = EntityMapping::id($params['entity_mapping_id'])
    ->read([
        'entity',
        'mapping_type',
        'column_mappings_ids' => [
            'origin_name',
            'origin_index',
            'target_name',
            'data_transformers_ids' => [
                'transformer_type',
                'transformer_subtype',
                'order',
                'value',
                'cast_to',
                'transform_by',
                'field_contains_value',
                'map_values_ids' => ['old_value', 'new_value']
            ]
        ]
    ])
    ->first(true);

if(is_null($entity_mapping)) {
    throw new Exception('unknown_entity_mapping', EQ_ERROR_UNKNOWN_OBJECT);
}

$model = $orm->getModel($entity_mapping['entity']);
if(!$model) {
    throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
}

if(empty($entity_mapping['column_mappings_ids'])) {
    throw new Exception('no_column_mappings', EQ_ERROR_INVALID_PARAM);
}

$map_col_mapping = $createMapColMapping(
    $entity_mapping,
    $params['data'],
    $params['indexation'] ?? []
);

$entities_data = [];
foreach($params['data'] as $row_index => $row) {
    $entity_data = [];
    foreach($row as $column_index => $column_data) {
        $column_mapping = $map_col_mapping[$column_index] ?? null;
        if(is_null($column_mapping)) {
            continue;
        }

        $f = $model->getField($column_mapping['target_name']);
        if(is_null($f)) {
            continue;
        }

        $value = $adapter->adaptIn($column_data, $f->getUsage());

        foreach($column_mapping['data_transformers_ids'] as $data_transformer) {
            $value = DataTransformer::transformValue($data_transformer, $value);
        }

        $entity_data[$column_mapping['target_name']] = $value;
    }

    if(!empty($entity_data)) {
        $entities_data[] = $entity_data;
    }
    else {
        trigger_error("PHP::model not imported for index $row_index", EQ_REPORT_WARNING);
    }
}

if(!empty($entities_data)) {
    eQual::run('do', 'core_model_import', [
        'entity'    => $entity_mapping['entity'],
        'data'      => $entities_data
    ]);
}

$context->httpResponse()
        ->status(204)
        ->send();
