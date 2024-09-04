<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\DataGenerator;
use equal\orm\UsageFactory;

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
 * @var \equal\php\Context      $context
 * @var \equal\orm\ObjectManager $orm
 */
list('context' => $context, 'orm' => $orm) = $providers;

/**
 * Methods
 */

$generateRecognizableFieldRandomValue = function($field) {
    switch($field) {
        case 'username':
            return DataGenerator::username();
        case 'firstname':
            return DataGenerator::firstname();
        case 'lastname':
            return DataGenerator::lastname();
        case 'fullname':
            return DataGenerator::fullname();
        case 'address_street':
            return DataGenerator::addressStreet();
        case 'address_zip':
            return DataGenerator::addressZip();
        case 'address_city':
            return DataGenerator::addressCity();
        case 'address_country':
            return DataGenerator::addressCountry();
        case 'address':
            return DataGenerator::address();
    }

    return null;
};

$generateFieldRandomValue = function($field_conf) {
    switch($field_conf['type']) {
        case 'string':
            if(!empty($field_conf['selection'])) {
                $values = array_values($field_conf['selection']);
                return $values[array_rand($values)];
            }
            elseif(isset($field_conf['default'])) {
                return $field_conf['default'];
            }

            return DataGenerator::plainText();
        case 'boolean':
            return DataGenerator::boolean();
        case 'integer':
            return DataGenerator::integer(9);
        case 'float':
            return DataGenerator::realNumber(9, 2);
    }

    return null;
};

/**
 * Action
 */

$new_entity = [];

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];
$recognizable_fields = [
    'username', 'firstname', 'lastname', 'fullname',
    'address', 'address_street', 'address_zip', 'address_city', 'address_country'
];

$schema = $model->getSchema();
foreach($schema as $field => $conf) {
    if(isset($params['fields'][$field])) {
        $new_entity[$field] = $params['fields'][$field];
    }

    if(
        in_array($field, $root_fields)
        || in_array($conf['type'], ['alias', 'computed', 'one2many'])
        || (in_array($conf['type'], ['many2one', 'many2many']) && !isset($params['relations'][$field]))
    ) {
        continue;
    }

    if($conf['type'] === 'many2one') {
        $ids = $conf['foreign_object']::search([])->ids();
        $mode = $params['relations'][$field]['mode'] ?? 'use-existing-or-create';
        if($mode === 'use-existing-or-create') {
            if(empty($ids)) {
                $mode = 'create';
            }
            else {
                $mode = mt_rand(0, 1) === 1 ? 'use-existing' : 'create';
            }
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
                $relation_param = [
                    'entity' => $conf['foreign_object']
                ];
                foreach(['fields', 'relations'] as $param_key) {
                    if(isset($params['relations'][$field][$param_key])) {
                        $relation_param[$param_key] = $params['relations'][$field][$param_key];
                    }
                }

                $result = eQual::run('do', 'core_model_generate', $relation_param);
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
                $relation_param = [
                    'entity'    => $conf['foreign_object'],
                    'lang'      => $params['lang']
                ];
                foreach(['fields', 'relations'] as $param_key) {
                    if(isset($params['relations'][$field][$param_key])) {
                        $relation_param[$param_key] = $params['relations'][$field][$param_key];
                    }
                }

                $new_relation_entities_ids = [];
                for($i = 0; $i < $qty; $i++) {
                    $result = eQual::run('do', 'core_model_generate', $relation_param);
                    $new_relation_entities_ids[] = $result['id'];
                }

                if(!empty($new_relation_entities_ids)) {
                    $new_entity[$field] = $new_relation_entities_ids;
                }
                break;
        }
    }
    elseif(isset($conf['usage'])) {
        $usage = UsageFactory::create($conf['usage']);
        $new_entity[$field] = $usage->generateRandomValue();
    }
    elseif(in_array($field, $recognizable_fields)) {
        $new_entity[$field] = $generateRecognizableFieldRandomValue($field);
    }
    else {
        $new_entity[$field] = $generateFieldRandomValue($conf);
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

    $relation_param = [
        'entity'    => $conf['foreign_object'],
        'lang'      => $params['lang']
    ];
    foreach(['fields', 'relations'] as $param_key) {
        if(isset($params['relations'][$field][$param_key])) {
            $relation_param[$param_key] = $params['relations'][$field][$param_key];
        }
    }

    if(!isset($relation_param['fields'])) {
        $relation_param['fields'] = [];
    }
    $relation_param['fields'][$conf['foreign_field']] = $instance['id'];

    $new_relation_entities_ids = [];
    for($i = 0; $i < $qty; $i++) {
        $result = eQual::run('do', 'core_model_generate', $relation_param);
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
