<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Author: The eQual Framework Contributors
    Original Author: Lucas Laurent
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\data\DataGenerator;
use equal\orm\Domain;

list($params, $providers) = eQual::announce([
    'description'   => "Generate a new object with random data and given values.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'qty' => [
            'description'   => 'The quantity of objects to generate. Supports both integer notation and array notation.',
            'type'          => 'string',
            'default'       => '1'
        ],
        'fields' =>  [
            'description'   => 'Associative array mapping fields to their related values.',
            'type'          => 'array',
            'default'       => []
        ],
        'relations' => [
            'description'   => 'Relational fields descriptor, telling how to handle the object relations (many2one, one2many and many2many).',
            'type'          => 'array',
            'default'       => []
        ],
        'set_object_data' => [
            'description'   => 'Associative array of the generated object data to add to the object.',
            'help'          => 'The object is accessible in domain like this "object.{field}"',
            'type'          => 'array',
            'default'       => []
        ],
        'object_data' => [
            'description'   => 'Current object data (carry).',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

/**
 * Methods
 */

$getQty = function($qty) {
    $result = 1;
    if(is_numeric($qty)) {
        $result = intval($qty);
    }
    elseif(!is_array($qty)) {
        // handle array notation
        $data = json_decode($qty);
        if($data && is_array($data) && count($data) === 2) {
            $qty = $data;
        }
    }

    if(is_array($qty) && count($qty) === 2) {
        $result = rand($qty[0], $qty[1]);
    }

    return $result;
};

$getRelationItemsIds = function(string $entity, array $relation_domain, array $object_data) use ($orm) {
    $model = $orm->getModel($entity);
    if(!$model) {
        throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
    }

    $domain = [];
    if(!empty($relation_domain)) {
        $domain = (new Domain($relation_domain))
            ->parse($object_data)
            ->toArray();
    }

    return $entity::search($domain)->ids();
};

$createGenerateParams = function(array $field_descriptor, array $relation_descriptor, array $object_data, string $lang) {
    $model_generate_params = [
        'entity'    => $field_descriptor['foreign_object'],
        'lang'      => $lang
    ];
    foreach(['qty', 'fields', 'relations', 'set_object_data'] as $param_key) {
        if(isset($relation_descriptor[$param_key])) {
            $model_generate_params[$param_key] = $relation_descriptor[$param_key];
        }
    }

    if(!empty($object_data)) {
        $model_generate_params['object_data'] = $object_data;
    }

    return $model_generate_params;
};

$generateMany2One = function(array $field_descriptor, array $relation_descriptor, string $lang, array $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_descriptor, $relation_descriptor, $object_data, $lang);
    return eQual::run('do', 'core_model_generate', $model_generate_params);
};

$generateMany2Many = function(array $field_descriptor, array $relation_descriptor, string $lang, array $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_descriptor, $relation_descriptor, $object_data, $lang);
    return eQual::run('do', 'core_model_generate', $model_generate_params);
};

$generateOne2Many = function(int $id, array $field_descriptor, array $relation_descriptor, string $lang, array $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_descriptor, $relation_descriptor, $object_data, $lang);
    if(!isset($model_generate_params['fields'])) {
        $model_generate_params['fields'] = [];
    }
    $model_generate_params['fields'][$field_descriptor['foreign_field']] = $id;
    return eQual::run('do', 'core_model_generate', $model_generate_params);
};

$addRelationResultToObjectData = function(array &$object_data, string $field, array $set_object_data, array $relation_result) {
    foreach(array_keys($set_object_data) as $key) {
        if(isset($relation_result['object_data'][$key])) {
            $object_data["$field.$key"] = $relation_result['object_data'][$key];
        }
    }
};

$addRelationResultsToObjectData = function(array &$object_data, string $field, array $set_object_data, array $relation_results) {
    $i = 0;
    foreach($relation_results as $relation_result) {
        foreach(array_keys($set_object_data) as $key) {
            if(isset($relation_result['object_data'][$key])) {
                $object_data["$field.$i.$key"] = $relation_result['object_data'][$key];
            }
        }
        $i++;
    }
};

/**
 * Action
 */

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];

$qty = $getQty($params['qty']);

// #todo: Handle multi columns unique (only single column unique is handled)
// #todo: Handle unique for many2one relations
$model_unique_conf = $model->getUnique();

$schema = $model->getSchema();

$results = [];
for($i = 0; $i < $qty; $i++) {
    $new_entity = [];
    foreach($schema as $field => $field_descriptor) {
        $field_value_forced = isset($params['fields'][$field]);
        $field_has_generate_function = isset($field_descriptor['generation']) && method_exists($params['entity'], $field_descriptor['generation']);

        if( !$field_value_forced
                && !$field_has_generate_function
                && ( in_array($field, $root_fields) || in_array($field_descriptor['type'], ['alias', 'computed', 'one2many', 'many2one', 'many2many']) ) ) {
            continue;
        }

        $is_required = $field_descriptor['required'] ?? false;

        $should_be_unique = $field_descriptor['unique'] ?? false;
        if(!$should_be_unique && !empty($model_unique_conf)) {
            foreach($model_unique_conf as $unique_conf) {
                if(count($unique_conf) === 1 && $unique_conf[0] === $field) {
                    $should_be_unique = true;
                    break;
                }
            }
        }

        $field_value_allowed = false;
        $unique_retry_count = 10;
        while($unique_retry_count > 0 && !$field_value_allowed) {
            $unique_retry_count--;

            if($field_value_forced) {
                $new_entity[$field] = $params['fields'][$field];
            }
            elseif($field_has_generate_function && method_exists($params['entity'], $field_descriptor['generation'])) {
                $new_entity[$field] = $params['entity']::{$field_descriptor['generation']}();
            }
            else {
                if(!$is_required && DataGenerator::boolean(0.05)) {
                    $new_entity[$field] = null;
                }
                else {
                    $new_entity[$field] = DataGenerator::generateFromField($field, $field_descriptor, $params['lang']);
                }
            }

            if($should_be_unique) {
                $ids = $params['entity']::search([$field, '=', $new_entity[$field]])->ids();
                if(empty($ids)) {
                    $field_value_allowed = true;
                }
                elseif($field_value_forced && $is_required) {
                    trigger_error("PHP::skip creation of {$params['entity']} because of value $new_entity[$field] for $field field.", EQ_REPORT_WARNING);
                    continue 3;
                }
            }
            else {
                $field_value_allowed = true;
            }
        }

        if(!$field_value_allowed) {
            if($is_required) {
                trigger_error("PHP::skip creation of {$params['entity']} because not able to generate a unique value for $field field.", EQ_REPORT_WARNING);
                continue 2;
            }
            else {
                unset($new_entity[$field]);
            }
        }
    }

    foreach($params['relations'] as $field => $relation_descriptor) {
        $field_descriptor = $schema[$field] ?? null;
        $field_type = $field_descriptor['result_type'] ?? $field_descriptor['type'] ?? null;
        if(is_null($field_descriptor) || !in_array($field_type, ['many2one', 'many2many'])) {
            continue;
        }

        $is_required = $field_descriptor['required'] ?? false;

        $ids = [];
        $mode = $relation_descriptor['mode'] ?? 'use-existing-or-create';
        if($mode !== 'create') {
            $ids = $getRelationItemsIds(
                $field_descriptor['foreign_object'],
                $relation_descriptor['domain'] ?? [],
                array_merge($new_entity, $params['object_data'])
            );
            if($mode === 'use-existing-or-create') {
                $mode = empty($ids) || DataGenerator::boolean() ? 'create' : 'use-existing';
            }
            elseif($mode === 'use-existing' && empty($ids)) {
                $mode = 'create';
                trigger_error("PHP::cannot use 'use-existing' for {$field_descriptor['foreign_object']} relation of {$params['entity']} because nothing to link, fallback on 'create'.", EQ_REPORT_WARNING);
            }
        }

        switch($field_type) {
            case 'many2one':
                switch($mode) {
                    case 'use-existing':
                        if(!$is_required) {
                            $ids[] = null;
                        }

                        $new_entity[$field] = $ids[array_rand($ids)];
                        break;
                    case 'create':
                        $relation_results = $generateMany2One($field_descriptor, $relation_descriptor, $params['lang'], $params['object_data']);
                        if(count($relation_results) === 1) {
                            $new_entity[$field] = $relation_results[0]['id'];

                            if(!empty($relation_descriptor['set_object_data'])) {
                                $addRelationResultToObjectData($params['object_data'], $field, $relation_descriptor['set_object_data'], $relation_results[0]);
                            }
                        }
                        elseif($is_required) {
                            trigger_error("PHP::skip creation of {$params['entity']} because not able to generate object for $field field.", EQ_REPORT_WARNING);
                            continue 4;
                        }
                        break;
                }
                break;
            case 'many2many':
                switch($mode) {
                    case 'use-existing':
                        $relation_qty = $getQty($relation_descriptor['qty']);

                        $random_ids = [];
                        for($j = 0; $j < $relation_qty; $j++) {
                            if(empty($ids)) {
                                break;
                            }

                            $random_index = array_rand($ids);
                            $random_ids[] = $ids[$random_index];
                            array_splice($ids, $random_index, 1);
                        }

                        if(!empty($random_ids)) {
                            $new_entity[$field] = $random_ids;
                        }
                        break;
                    case 'create':
                        $relation_results = $generateMany2Many($field_descriptor, $relation_descriptor, $params['lang'], $params['object_data']);

                        $new_relation_entities_ids = array_column($relation_results, 'id');
                        if(!empty($new_relation_entities_ids)) {
                            $new_entity[$field] = $new_relation_entities_ids;

                            if(!empty($relation_descriptor['set_object_data'])) {
                                $addRelationResultsToObjectData($params['object_data'], $field, $relation_descriptor['set_object_data'], $relation_results);
                            }
                        }
                        break;
                }
                break;
        }
    }

    $field_to_read = array_values($params['set_object_data']);

    $instance = $params['entity']::create($new_entity, $params['lang'])
        ->read($field_to_read)
        ->adapt('json')
        ->first(true);

    foreach($params['set_object_data'] as $key => $field) {
        if(isset($instance[$field])) {
            $params['object_data'][$key] = $instance[$field];
        }
    }

    foreach($params['relations'] as $field => $relation_descriptor) {
        $field_descriptor = $schema[$field] ?? null;
        $field_type = $field_descriptor['result_type'] ?? $field_descriptor['type'] ?? null;
        if(is_null($field_descriptor) || $field_type !== 'one2many') {
            continue;
        }

        $relation_results = $generateOne2Many($instance['id'], $field_descriptor, $relation_descriptor, $params['lang'], $params['object_data']);

        if(!empty($relation_descriptor['set_object_data']) && !empty($relation_results)) {
            $addRelationResultsToObjectData($params['object_data'], $field, $relation_descriptor['set_object_data'], $relation_results);
        }
    }

    $results[] = [
        'entity'        => $params['entity'],
        'id'            => $instance['id'],
        'object_data'   => $params['object_data']
    ];
}

$context->httpResponse()
        ->status(201)
        ->body($results)
        ->send();
