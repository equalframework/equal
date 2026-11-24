<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Author: The eQual Framework Contributors
    Original Author: Lucas Laurent
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\DataGenerator;

[$params, $providers] = eQual::announce([
    'description'   => "Anonymize an existing object with random data and given values.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'required'      => true
        ],
        'fields' => [
            'type'          => 'array',
            'description'   => 'List of fields that must be anonymized.',
            'default'       => []
        ],
        'relations' => [
            'type'          => 'array',
            'description'   => 'How to handle the object relations (many2one and one2many)',
            'default'       => []
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Specific language for multilang field.',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'type'          => 'array',
            'description'   => 'Criteria that results have to match (series of conjunctions)',
            'default'       => []
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

$anonymized_values = [];

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$schema = $model->getSchema();

$ids = $params['entity']::search($params['domain'])->ids();
foreach($ids as $id) {
    foreach($schema as $field => $descriptor) {
        if(!($descriptor['sensitive'] ?? false) && !in_array($field, $params['fields'])) {
            continue;
        }

        if(isset($descriptor['generation']) && method_exists($params['entity'], $descriptor['generation'])) {
            $anonymized_values[$field] = $params['entity']::{$descriptor['generation']}();
        }
        else {
            $required = $descriptor['required'] ?? false;
            if(!$required && DataGenerator::boolean(0.05)) {
                $anonymized_values[$field] = null;
            }
            else {
                $anonymized_values[$field] = DataGenerator::generateFromField($field, $descriptor, $params['lang']);
            }
        }
    }

    if(!empty($anonymized_values)) {
        $instance = $params['entity']::id($id)
            ->update($anonymized_values)
            ->read(['id'])
            ->first();
    }

    foreach($schema as $field => $descriptor) {
        if(!in_array($descriptor['type'], ['one2many', 'many2one']) || !isset($params['relations'][$field])) {
            continue;
        }

        $relation_params = [
            'entity'    => $descriptor['foreign_object'],
            'lang'      => $params['lang']
        ];

        foreach(['fields', 'relations', 'domain'] as $param_key) {
            if(isset($params['relations'][$field][$param_key])) {
                $relation_params[$param_key] = $params['relations'][$field][$param_key];
            }
        }

        if(!isset($relation_params['domain'])) {
            $relation_params['domain'] = [];
        }

        switch($descriptor['type']) {
            case 'one2many':
                $relation_params['domain'][] = [$descriptor['foreign_field'], '=', $id];
                break;
            case 'many2one':
                $instance = $params['entity']::id($id)
                    ->read([$field])
                    ->first();

                if(!is_null($instance[$field])) {
                    $relation_params['domain'][] = ['id', '=', $instance[$field]];
                }
                break;
        }

        eQual::run('do', 'core_model_anonymize', $relation_params);
    }
}

$context
    ->httpResponse()
    ->status(204)
    ->send();
