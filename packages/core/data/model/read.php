<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Lists objects of provided entity and ids with requested fields.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (with namespace) of requested entity.',
            'required'      => true
        ],
        'ids' => [
            'type'          => 'array',
            'description'   => 'List of unique identifiers of the objects to read.',
            'required'      => true
        ],
        'fields' => [
            'type'          => 'array',
            'description'   => 'Names of fields for which value is requested.',
            'default'       => ['id', 'name']
        ],
        'lang' => [
            'type'          => 'string',
            'usage'         => 'language/iso-639',
            'description'   => 'Language in which to retrieve multilang fields.',
            'default'       => constant('DEFAULT_LANG')
        ],
        'order' => [
            'type'          => 'string',
            'description'   => 'Column to use for sorting results.',
            'default'       => 'id'
        ],
        'sort' => [
            'type'          => 'string',
            'description'   => 'Direction of the sorting.',
            'selection'     => ['asc', 'desc'],
            'default'       => 'asc'
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => [ 'context', 'orm' ]
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

// get the complete schema of the object (including special fields)
$schema = $entity->getSchema();

// adapt received fields names for dot notation support
$fields = [];
foreach($params['fields'] as $key => $field) {
    if(gettype($field) != 'string') {
        if(!is_numeric($key)) {
            $fields[$key] = (array) $field;
        }
        continue;
    }
    // handle dot notation: convert to array notation
    if(strpos($field, '.')) {
        $parts = explode('.', $field);
        if(!isset($schema[$parts[0]])) {
            continue;
        }
        $target = &$fields;
        while(count($parts) > 1) {
            $field = array_shift($parts);
            if(!isset($target[$field])) {
                $target[$field] = [];
            }
            $target = &$target[$field];
        }
        $target[] = array_shift($parts);
    }
    // regular field name
    else if(isset($schema[$field])) {
        $fields[] = $field;
    }
}

// make sure 'name' is always requested
$fields[] = 'name';

// get a sorted collection of objects
$collection = $params['entity']::search([['id', 'in', $params['ids']], ['state', '<>', 'unknown']], [ 'sort' => [ $params['order'] => $params['sort'] ] ]);

$result = $collection
    ->read($fields, $params['lang'])
    ->adapt('json')
    // return result as an array
    // #memo - JSON objects handled by ES2015+ might have their keys order altered
    ->get(true);

$context
    ->httpResponse()
    ->body($result)
    ->send();