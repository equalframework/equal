<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
        'fields' =>  [
            'description'   => 'Associative array mapping fields to their related values.',
            'type'          => 'array',
            'default'       => []
        ],
        'relations' => [
            'description'   => 'How to handle the object relations (many2one, one2many and many2many)',
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
            'description'   => 'Current object data.',
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
list('context' => $context, 'orm' => $orm) = $providers;

/**
 * Methods
 */

$getRelationItemsIds = function($item_class, $relation_domain, $object_data) {
    $domain = [];
    if(!empty($relation_domain)) {
        $domain = (new Domain($relation_domain))
            ->parse($object_data)
            ->toArray();
    }

    return $item_class::search($domain)->ids();
};

$createGenerateParams = function($field_conf, $relation_conf, $object_data, $lang) {
    $model_generate_params = [
        'entity'    => $field_conf['foreign_object'],
        'lang'      => $lang
    ];
    foreach(['fields', 'relations', 'set_object_data'] as $param_key) {
        if(isset($relation_conf[$param_key])) {
            $model_generate_params[$param_key] = $relation_conf[$param_key];
        }
    }

    if(!empty($object_data)) {
        $model_generate_params['object_data'] = $object_data;
    }

    return $model_generate_params;
};

$generateMany2One = function($field_conf, $relation_conf, $lang, $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_conf, $relation_conf, $object_data, $lang);

    return eQual::run('do', 'core_model_generate', $model_generate_params);
};

$generateMany2Many = function($qty, $field_conf, $relation_conf, $lang, $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_conf, $relation_conf, $object_data, $lang);

    $results = [];
    for($i = 0; $i < $qty; $i++) {
        $results[] = eQual::run('do', 'core_model_generate', $model_generate_params);
    }

    return $results;
};

$generateOne2Many = function($id, $qty, $field_conf, $relation_conf, $lang, $object_data) use ($createGenerateParams) {
    $model_generate_params = $createGenerateParams($field_conf, $relation_conf, $object_data, $lang);

    if(!isset($model_generate_params['fields'])) {
        $model_generate_params['fields'] = [];
    }
    $model_generate_params['fields'][$field_conf['foreign_field']] = $id;

    $results = [];
    for($i = 0; $i < $qty; $i++) {
        $results[] = eQual::run('do', 'core_model_generate', $model_generate_params);
    }

    return $results;
};

/**
 * Action
 */

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$new_entity = [];
$root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];

$model_unique_conf = [];
if(method_exists($params['entity'], 'getUnique')) {
    $model_unique_conf = (new $params['entity'])->getUnique();
}

$schema = $model->getSchema();
foreach($schema as $field => $field_conf) {
    $field_value_forced = isset($params['fields'][$field]);
    $field_has_generate_function = isset($field_conf['generate']) && method_exists($params['entity'], $field_conf['generate']);

    if(
        !$field_value_forced
        && !$field_has_generate_function
        && (
            in_array($field, $root_fields)
            || in_array($field_conf['type'], ['alias', 'computed', 'one2many', 'many2one', 'many2many'])
        )
    ) {
        continue;
    }

    $should_be_unique = $field_conf['unique'] ?? false;
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
        elseif($field_has_generate_function) {
            $new_entity[$field] = $params['entity']::{$field_conf['generate']}();
        }
        else {
            $required = $field_conf['required'] ?? false;
            if(!$required && DataGenerator::boolean(0.05)) {
                $new_entity[$field] = null;
            }
            else {
                $new_entity[$field] = DataGenerator::generateByFieldConf($field, $field_conf, $params['lang']);
            }
        }

        if($should_be_unique) {
            $ids = $params['entity']::search([$field, '=', $new_entity[$field]])->ids();
            if(empty($ids)) {
                $field_value_allowed = true;
            }
        }
        else {
            $field_value_allowed = true;
        }
    }

    if(!$field_value_allowed) {
        unset($new_entity[$field]);
    }
}

foreach($params['relations'] as $field => $relation_conf) {
    $field_conf = $schema[$field] ?? null;
    $field_type = $field_conf['result_type'] ?? $field_conf['type'] ?? null;
    if(is_null($field_conf) || !in_array($field_type, ['many2one', 'many2many'])) {
        continue;
    }

    $mode = $relation_conf['mode'] ?? 'use-existing-or-create';
    if($mode !== 'create') {
        $ids = $getRelationItemsIds(
            $field_conf['foreign_object'],
            $relation_conf['domain'] ?? [],
            array_merge($new_entity, $params['object_data'])
        );
        if($mode === 'use-existing-or-create') {
            $mode = empty($ids) || DataGenerator::boolean() ? 'create' : 'use-existing';
        }
    }

    switch($field_type) {
        case 'many2one':
            switch($mode) {
                case 'use-existing':
                    if(!empty($ids)) {
                        if(!($field_conf['required'] ?? false)) {
                            $ids[] = null;
                        }

                        $new_entity[$field] = $ids[array_rand($ids)];
                    }
                    break;
                case 'create':
                    $result = $generateMany2One($field_conf, $relation_conf, $params['lang'], $params['object_data']);

                    $new_entity[$field] = $result['id'];

                    foreach(array_keys($relation_conf['set_object_data'] ?? []) as $key) {
                        if(isset($result['object_data'][$key])) {
                            $params['object_data']["$field.$key"] = $result['object_data'][$key];
                        }
                    }
                    break;
            }
            break;
        case 'many2many':
            $qty_conf = $relation_conf['qty'];
            $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

            switch($mode) {
                case 'use-existing':
                    $random_ids = [];
                    for($i = 0; $i < $qty; $i++) {
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
                    $results = $generateMany2Many($qty, $field_conf, $relation_conf, $params['lang'], $params['object_data']);

                    $new_relation_entities_ids = array_column($results, 'id');
                    if(!empty($new_relation_entities_ids)) {
                        $new_entity[$field] = $new_relation_entities_ids;
                    }

                    $i = 0;
                    foreach($results as $result) {
                        foreach(array_keys($relation_conf['set_object_data'] ?? []) as $key) {
                            if(isset($result['object_data'][$key])) {
                                $params['object_data']["$field.$i.$key"] = $result['object_data'][$key];
                            }
                        }
                        $i++;
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

foreach($params['relations'] as $field => $relation_conf) {
    $field_conf = $schema[$field] ?? null;
    $field_type = $field_conf['result_type'] ?? $field_conf['type'] ?? null;
    if(is_null($field_conf) || $field_type !== 'one2many') {
        continue;
    }

    $qty_conf = $relation_conf['qty'];
    $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

    $results = $generateOne2Many($instance['id'], $qty, $field_conf, $relation_conf, $params['lang'], $params['object_data']);

    $new_relation_entities_ids = array_column($results, 'id');
    if(!empty($new_relation_entities_ids)) {
        $new_entity[$field] = $new_relation_entities_ids;
    }

    $i = 0;
    foreach($results as $result) {
        foreach(array_keys($relation_conf['set_object_data'] ?? []) as $key) {
            if(isset($result['object_data'][$key])) {
                $params['object_data']["$field.$i.$key"] = $result['object_data'][$key];
            }
        }
        $i++;
    }
}

$result = [
    'entity'        => $params['entity'],
    'id'            => $instance['id'],
    'object_data'   => $params['object_data']
];

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
