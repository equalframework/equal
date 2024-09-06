<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\DataGenerator;

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
 * Action
 */

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
foreach($schema as $field => $conf) {
    $field_value_forced = isset($params['fields'][$field]);
    $field_has_generate_function = isset($conf['generate']) && method_exists($params['entity'], $conf['generate']);

    if(
        !$field_value_forced
        && !$field_has_generate_function
        && (
            in_array($field, $root_fields)
            || in_array($conf['type'], ['alias', 'computed', 'one2many'])
            || (in_array($conf['type'], ['many2one', 'many2many']) && !isset($params['relations'][$field]))
        )
    ) {
        continue;
    }

    $should_be_unique = $conf['unique'] ?? false;
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
            $new_entity[$field] = $params['entity']::{$conf['generate']}();
        }
        elseif($conf['type'] === 'many2one') {
            $ids = $conf['foreign_object']::search([])->ids();
            $mode = $params['relations'][$field]['mode'] ?? 'use-existing-or-create';
            if($mode === 'use-existing-or-create') {
                $mode = empty($ids) || DataGenerator::boolean() ? 'create' : 'use-existing';
            }

            switch($mode) {
                case 'use-existing':
                    if(!empty($ids)) {
                        if(!($conf['required'] ?? false)) {
                            $ids[] = null;
                        }

                        $new_entity[$field] = $ids[array_rand($ids)];
                    }
                    break;
                case 'create':
                    $relation_params = [
                        'entity' => $conf['foreign_object']
                    ];
                    foreach(['fields', 'relations'] as $param_key) {
                        if(isset($params['relations'][$field][$param_key])) {
                            $relation_params[$param_key] = $params['relations'][$field][$param_key];
                        }
                    }

                    $result = eQual::run('do', 'core_model_generate', $relation_params);
                    $new_entity[$field] = $result['id'];
                    break;
            }
        }
        elseif($conf['type'] === 'many2many') {
            $mode = $params['relations'][$field]['mode'] ?? 'use-existing-or-create';

            $qty_conf = $params['relations'][$field]['qty'] ?? [0, 5];
            $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

            switch($mode) {
                case 'use-existing':
                    $ids = $conf['foreign_object']::search([])->ids();
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
                    $relation_params = [
                        'entity'    => $conf['foreign_object'],
                        'lang'      => $params['lang']
                    ];
                    foreach(['fields', 'relations'] as $param_key) {
                        if(isset($params['relations'][$field][$param_key])) {
                            $relation_params[$param_key] = $params['relations'][$field][$param_key];
                        }
                    }

                    $new_relation_entities_ids = [];
                    for($i = 0; $i < $qty; $i++) {
                        $result = eQual::run('do', 'core_model_generate', $relation_params);
                        $new_relation_entities_ids[] = $result['id'];
                    }

                    if(!empty($new_relation_entities_ids)) {
                        $new_entity[$field] = $new_relation_entities_ids;
                    }
                    break;
            }
        }
        else {
            $required = $conf['required'] ?? false;
            if(!$required && DataGenerator::boolean(0.05)) {
                $new_entity[$field] = null;
            }
            else {
                $new_entity[$field] = DataGenerator::generateByFieldConf($field, $conf, $params['lang']);
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

$instance = $params['entity']::create($new_entity, $params['lang'])
    ->adapt('json')
    ->first(true);

foreach($schema as $field => $conf) {
    if($conf['type'] !== 'one2many' || !isset($params['relations'][$field])) {
        continue;
    }

    $qty_conf = $params['relations'][$field]['qty'] ?? [0, 3];
    $qty = is_array($qty_conf) ? mt_rand($qty_conf[0], $qty_conf[1]) : $qty_conf;

    $relation_params = [
        'entity'    => $conf['foreign_object'],
        'lang'      => $params['lang']
    ];
    foreach(['fields', 'relations'] as $param_key) {
        if(isset($params['relations'][$field][$param_key])) {
            $relation_params[$param_key] = $params['relations'][$field][$param_key];
        }
    }

    if(!isset($relation_params['fields'])) {
        $relation_params['fields'] = [];
    }
    $relation_params['fields'][$conf['foreign_field']] = $instance['id'];

    $new_relation_entities_ids = [];
    for($i = 0; $i < $qty; $i++) {
        $result = eQual::run('do', 'core_model_generate', $relation_params);
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
