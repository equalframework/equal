<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\DataGenerator;

list($params, $providers) = eQual::announce([
    'description'   => "Anonymize an existing object with random data and given values.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'List of fields that must be anonymized.',
            'type'          => 'array',
            'default'       => []
        ],
        'relations' => [
            'description'   => 'How to handle the object relations (many2one and one2many)',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'description'   => 'Criteria that results have to match (series of conjunctions)',
            'type'          => 'array',
            'default'       => []
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

$anonymized_values = [];

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$schema = $model->getSchema();

$ids = $params['entity']::search($params['domain'])->ids();
foreach($ids as $id) {
    foreach($schema as $field => $conf) {
        if(!($conf['sensitive'] ?? false) && !in_array($field, $params['fields'])) {
            continue;
        }

        if(isset($conf['generate']) && method_exists($params['entity'], $conf['generate'])) {
            $anonymized_values[$field] = $params['entity']::{$conf['generate']}();
        }
        else {
            $required = $conf['required'] ?? false;
            if(!$required && DataGenerator::boolean(0.05)) {
                $anonymized_values[$field] = null;
            }
            else {
                $anonymized_values[$field] = DataGenerator::generateByFieldConf($field, $conf, $params['lang']);
            }
        }
    }

    if(!empty($anonymized_values)) {
        $instance = $params['entity']::id($id)
            ->update($anonymized_values)
            ->read(['id'])
            ->first();
    }

    foreach($schema as $field => $conf) {
        if(!in_array($conf['type'], ['one2many', 'many2one']) || !isset($params['relations'][$field])) {
            continue;
        }

        $relation_params = [
            'entity'    => $conf['foreign_object'],
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

        switch($conf['type']) {
            case 'one2many':
                $relation_params['domain'][] = [$conf['foreign_field'], '=', $id];
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

$context->httpResponse()
        ->status(204)
        ->send();
