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
        'add_to_domain_data' => [
            'description'   => 'Global domain data.',
            'type'          => 'array'
        ],
        'domain_data' => [
            'description'   => 'Global domain data.',
            'type'          => 'array'
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

$new_entity = [];

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

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
            || in_array($field_conf['type'], ['alias', 'computed', 'one2many'])
            || in_array($field_conf['type'], ['many2one', 'many2many'])
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
    if(!isset($schema[$field]) || !in_array($schema[$field]['type'], ['many2one', 'many2many'])) {
        continue;
    }

    $field_conf = $schema[$field];

    switch($field_conf['type']) {
        case 'many2one':
            $domain = [];
            if($relation_conf['domain']) {
                $domain = (new Domain($relation_conf['domain']))
                    ->parse(array_merge($new_entity, $params['domain_data'] ?? []))
                    ->toArray();
            }

            $ids = $field_conf['foreign_object']::search($domain)->ids();
            $mode = $relation_conf['mode'] ?? 'use-existing-or-create';
            if($mode === 'use-existing-or-create') {
                $mode = empty($ids) || DataGenerator::boolean() ? 'create' : 'use-existing';
            }

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
                    $model_generate_params = [
                        'entity' => $field_conf['foreign_object']
                    ];
                    foreach(['fields', 'relations'] as $param_key) {
                        if(isset($relation_conf[$param_key])) {
                            $model_generate_params[$param_key] = $relation_conf[$param_key];
                        }
                    }

                    if(!empty($params['domain_data'])) {
                        $model_generate_params['domain_data'] = $params['domain_data'];
                    }

                    $result = eQual::run('do', 'core_model_generate', $model_generate_params);
                    $new_entity[$field] = $result['id'];
                    break;
            }
            break;
        case 'many2many':
            $mode = $relation_conf['mode'] ?? 'use-existing-or-create';

            $qty_conf = $relation_conf['qty'] ?? [0, 5];
            $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

            switch($mode) {
                case 'use-existing':
                    $domain = [];
                    if($relation_conf['domain']) {
                        $domain = (new Domain($relation_conf['domain']))
                            ->parse(array_merge($new_entity, $params['domain_data'] ?? []))
                            ->toArray();
                    }

                    $ids = $field_conf['foreign_object']::search($domain)->ids();
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
                    $model_generate_params = [
                        'entity'    => $field_conf['foreign_object'],
                        'lang'      => $params['lang']
                    ];
                    foreach(['fields', 'relations'] as $param_key) {
                        if(isset($relation_conf[$param_key])) {
                            $model_generate_params[$param_key] = $relation_conf[$param_key];
                        }
                    }

                    $new_relation_entities_ids = [];
                    for($i = 0; $i < $qty; $i++) {
                        $result = eQual::run('do', 'core_model_generate', $model_generate_params);
                        $new_relation_entities_ids[] = $result['id'];
                    }

                    if(!empty($params['domain_data'])) {
                        $model_generate_params['domain_data'] = $params['domain_data'];
                    }

                    if(!empty($new_relation_entities_ids)) {
                        $new_entity[$field] = $new_relation_entities_ids;
                    }
                    break;
            }
            break;
    }
}

$field_to_read = [];
if(isset($params['add_to_domain_data'])) {
    $field_to_read = array_values($params['add_to_domain_data']);
}

$instance = $params['entity']::create($new_entity, $params['lang'])
    ->read($field_to_read)
    ->adapt('json')
    ->first(true);

$domain_data = $params['domain_data'] ?? [];
foreach($params['add_to_domain_data'] ?? [] as $key => $field) {
    if(isset($instance[$field])) {
        $domain_data[$key] = $instance[$field];
    }
}

foreach($params['relations'] as $field => $relation_conf) {
    if(!isset($schema[$field]) || $schema[$field]['type'] !== 'one2many') {
        continue;
    }

    $field_conf = $schema[$field];

    $qty_conf = $relation_conf['qty'] ?? [0, 3];
    $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

    $model_generate_params = [
        'entity'    => $field_conf['foreign_object'],
        'lang'      => $params['lang']
    ];
    foreach(['fields', 'relations', 'add_to_domain_data'] as $param_key) {
        if(isset($relation_conf[$param_key])) {
            $model_generate_params[$param_key] = $relation_conf[$param_key];
        }
    }

    if(!isset($model_generate_params['fields'])) {
        $model_generate_params['fields'] = [];
    }
    $model_generate_params['fields'][$field_conf['foreign_field']] = $instance['id'];

    if(!empty($domain_data)) {
        $model_generate_params['domain_data'] = $domain_data;
    }

    $new_relation_entities_ids = [];
    for($i = 0; $i < $qty; $i++) {
        $result = eQual::run('do', 'core_model_generate', $model_generate_params);
        $new_relation_entities_ids[] = $result['id'];
    }

    if(!empty($new_relation_entities_ids)) {
        $new_entity[$field] = $new_relation_entities_ids;
    }
}

$result = [
    'entity'    => $params['entity'],
    'id'        => $instance['id']
];

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
