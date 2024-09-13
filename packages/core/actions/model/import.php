<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\import\EntityMapping;

list($params, $providers) = eQual::announce([
    'description' => 'Import eQual model data from external source.',
    'params'      => [
        'entity_mapping_id' => [
            'type'          => 'integer',
            'description'   => 'The ID of the entity mapping configuration to use.',
            'min'           => 1,
            'required'      => true
        ],
        'origin_data_rows' => [
            'type'          => 'array',
            'description'   => 'The data that needs to be imported.',
            'required'      => true
        ],
        'origin_data_columns_indexation' => [
            'type'          => 'array',
            'description'   => 'List of field keys that describe the indexation of origin data rows.',
            'help'          => 'Must be a list of strings. If empty the mapping is done on column index.'
        ]
    ],
    'response'    => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'   => ['context']
]);

list('context' => $context) = $providers;

if(empty($params['origin_data_rows'][0])) {
    throw new Exception('empty_origin_data_rows', EQ_ERROR_INVALID_PARAM);
}

$entity_mapping = EntityMapping::id($params['entity_mapping_id'])
    ->read([
        'entity',
        'column_mappings_ids' => [
            'origin_name',
            'origin_index',
            'origin_cast_type',
            'target_name',
            'data_transformers_ids' => [
                'transformer_type',
                'transformer_subtype',
                'order'
            ]
        ]
    ])
    ->first(true);

if(is_null($entity_mapping)) {
    throw new Exception('unknown_entity_mapping', EQ_ERROR_UNKNOWN_OBJECT);
}

if(empty($entity_mapping['column_mappings_ids'])) {
    throw new Exception('no_column_mappings', EQ_ERROR_INVALID_PARAM);
}

$map_col_mapping = [];

$is_index_mapping = isset($params['origin_data_rows'][0][0]);
if($is_index_mapping) {
    if(!empty($params['origin_data_columns_indexation'])) {
        $not_string_keys = array_filter(
            $params['origin_data_columns_indexation'],
            function($key) {
                return !is_string($key);
            }
        );

        if(!empty($not_string_keys)) {
            throw new Exception('origin_data_columns_indexation_must_be_an_array_of_strings', EQ_ERROR_INVALID_PARAM);
        }

        foreach($params['origin_data_columns_indexation'] as $column_key) {
            foreach($entity_mapping['column_mappings_ids'] as $col_mapping) {
                if($col_mapping['origin_name'] === $column_key) {
                    $map_col_mapping[] = $col_mapping;
                }
            }
        }
    }
    else {
        foreach(array_keys($params['origin_data_rows'][0]) as $index) {
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
    }
}
else {
    foreach(array_keys($params['origin_data_rows'][0]) as $column_key) {
        foreach($entity_mapping['column_mappings_ids'] as $col_mapping) {
            if($col_mapping['origin_name'] === $column_key) {
                $map_col_mapping[$column_key] = $col_mapping;
            }
        }
    }
}

foreach($params['origin_data_rows'] as $row_index => $row) {
    $new_entity = [];
    foreach($row as $column_index => $column_data) {
        $column_mapping = $map_col_mapping[$column_index] ?? null;
        if(is_null($column_mapping)) {
            continue;
        }

        $new_entity[$column_mapping['target_name']] = $column_data;
    }

    if(!empty($new_entity)) {
        $entity_mapping['entity']::create($new_entity);
    }
    else {
        trigger_error("PHP::model not imported for index $row_index", EQ_REPORT_WARNING);
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
