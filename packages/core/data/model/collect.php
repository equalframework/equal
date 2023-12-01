<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;
use equal\orm\Field;

list($params, $providers) = announce([
    'description'   => 'Returns a list of entities according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'usage'         => 'orm/entity',
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Requested fields. If not specified, only \'id\' and \'name\' fields are returned.',
            'type'          => 'array',
            'default'       => ['id', 'name']
        ],
        'lang' =>  [
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ],
        'domain' => [
            'description'   => 'Criterias that results have to match (series of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ],
        'order' => [
            'description'   => 'Column(s) to use for sorting results.',
            'type'          => 'string',
            'default'       => 'id'
        ],
        'sort' => [
            'description'   => 'The direction  (i.e. \'asc\' or \'desc\').',
            'type'          => 'string',
            'default'       => 'asc'
        ],
        'start' => [
            'description'   => 'The row from which results have to start.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'limit' => [
            'description'   => 'The maximum number of results.',
            'type'          => 'integer',
            'min'           => 1,
            'max'           => 500,
            'default'       => 25
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'orm', 'adapt' ]
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $orm, $dap) = [ $providers['context'], $providers['orm'], $providers['adapt'] ];

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

/*
    Handle controller entities
*/
// #todo - standardize the way controllers are referred to as entities
$entity = str_replace('_', '\\', $params['entity']);
$parts = explode('\\', $entity);
$file = array_pop($parts);
if(ctype_lower(substr($file, 0, 1))) {
    $package = array_shift($parts);
    $path = implode('/', $parts);
    if(!file_exists(QN_BASEDIR."/packages/{$package}/data/{$path}/{$file}.php")) {
        throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
    }
    $operation = str_replace('\\', '_', $params['entity']);
    // retrieve announcement of target controller
    $data = eQual::run('get', $operation, ['announce' => true]);
    $controller_schema = isset($data['announcement']['params'])?$data['announcement']['params']:[];
    $requested_fields = array_map(function($a) { return explode('.', $a)[0]; }, $params['fields'] );
    // generate a virtual (empty) object
    $object = ['id' => 0];
    foreach($requested_fields as $field) {
        if(!isset($controller_schema[$field])) {
            continue;
        }
        $value = null;
        if(isset($controller_schema[$field]['default'])) {
            $f = new Field($controller_schema[$field]);
            $value = $adapter->adaptOut($controller_schema[$field]['default'], $f->getUsage());
        }
        $object[$field] = $value;
    }
    // send single-item collection as response
    $context->httpResponse()
            ->header('X-Total-Count', 1)
            ->body([$object])
            ->send();
    exit();
}

/*
    Handle Model entities
*/
// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
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

$domain = $params['domain'];

// if `deleted` field is requested, we need to force searching amongst deleted objects as well
if(in_array('deleted', $params['fields'])) {
    $domain = Domain::conditionAdd($domain, ['deleted', 'in', [0, 1]]);
}

// if domain contains a condition that targets `id` field, force searching regardless the state (this is the case for form views)
$has_id_clause = false;
foreach($domain as $clause) {
    if(is_array($clause)) {
        foreach($clause as $condition) {
            if(is_array($condition)) {
                $has_id_clause = ($condition[0] == 'id');
            }
            else {
                $has_id_clause = ($condition == 'id');
            }
        }
    }
    else {
        $has_id_clause = ($clause == 'id');
    }
}
if($has_id_clause) {
    $domain = Domain::conditionAdd($domain, ['state', '<>', 'unknown']);
}

// convert sorting comma notation to a map ([order_field => sort_direction, ...])
$sort = [];
$sort_parts  = explode(',', str_replace(' ', '', $params['sort']));
$order_parts = explode(',', str_replace(' ', '', $params['order']));

foreach($order_parts as $index => $order) {
    $order_sort = (count($sort_parts) > $index)? $sort_parts[$index] : $sort_parts[count($sort_parts)-1];
    $sort[$order] = $order_sort;
}

$collection = $params['entity']::search($domain, [ 'sort' => $sort ]);

$total = count($collection->ids());

// retrieve list as an array
// #memo - JSON objects handled by ES2015+ might have their keys order altered, so returning result as a map is not safe
$result = $collection
        ->shift($params['start'])
        ->limit($params['limit'])
        ->read($fields, $params['lang'])
        ->adapt('json')
        ->get(true);

$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();