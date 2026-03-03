<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;
use equal\orm\DomainCondition;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns a list of entities according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name of the entity to collect.',
            'help'          => "The entity is either a class or a controller, given with its full namespace (including the package).
                                Classes are given with backslash separators (e.g. 'core\\User'),
                                while controllers are given with underscore separators (e.g. 'core_model_collect').
                                If a controller is given, it is expected to be a data handler (GET).",
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
            'max'           => 2500,
            'default'       => 25
        ],
        'nolimit' => [
            'description'   => 'Explicit request for ignoring limit and return all matching objects.',
            'help'          => 'When activated start and limit parameters are ignored.',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => [ 'context', 'orm' ]
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

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
    if(!file_exists(EQ_BASEDIR."/packages/{$package}/data/{$path}/{$file}.php")) {
        throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
    }
    $operation = str_replace('\\', '_', $params['entity']);
    // retrieve announcement of target controller
    try {
        // #memo in case of announce as root (required for relaying params), an Exception with code 0 is raised and JSON is directly sent to stdout
        ob_start();
        eQual::run('get', $operation, array_merge($params, ['announce' => true]), true);
    }
    catch(Exception $e) {
        // ignore
    }
    finally {
        $json = ob_get_clean();
        $data = json_decode($json, true);
    }
    $controller_schema = $data['announcement']['params'] ?? [];
    $requested_fields = array_map(function($a) { return explode('.', $a)[0]; }, (array) $params['fields'] );
    // generate a virtual (empty) object
    $object = ['id' => 0];
    foreach($requested_fields as $field) {
        if(!isset($controller_schema[$field])) {
            continue;
        }
        $value = null;
        $descriptor = $controller_schema[$field];
        if(isset($descriptor['default'], $descriptor['type'])) {
            // #memo - return value from a eQual::run call is an array containing values matching the content-type of the controller
            if($descriptor['type'] === 'many2one') {
                $default = $descriptor['foreign_object']::id($descriptor['default'])->read(['id', 'name'])->get(true);
                $value = current($default);
            }
            else {
                // we assume targeted controller use 'application/json' content-type
                $value = $descriptor['default'];
            }
        }
        $object[$field] = $value;
    }
    // send single-item collection as response
    $context->httpResponse()
            ->header('X-Total-Count', 1)
            ->body([$object])
            ->send();
    exit(0);
}

/*
    Handle Model entities
*/
// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

// get the complete schema of the object (including special fields)
$schema = $entity->getSchema();

// adapt received fields names (non recursive m2o fields & dot notation support)
$fields = [];
foreach($params['fields'] as $key => $field) {
    if(gettype($field) != 'string') {
        if(is_numeric($key) || !isset($schema[$key])) {
            continue;
        }
        if(is_array($field)) {
            $fields[$key] = $field;
        }
        else {
            // `$key` is the field name : ignore value
            $fields[] = $key;
        }
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
    elseif(isset($schema[$field])) {
        $fields[] = $field;
    }
}

// ensure 'name' is always requested
$fields[] = 'name';

$domain = new Domain($params['domain']);

// if `deleted` field is requested, we need to force searching amongst deleted objects as well
if(in_array('deleted', $params['fields'])) {
    $domain->addCondition(new DomainCondition('deleted', 'in', [0, 1]));
}

// if domain contains a condition that targets `id` field, force searching regardless the state (this is the case for form views)
$has_id_clause = false;
foreach($domain->getClauses() as $clause) {
    foreach($clause->getConditions() as $condition) {
        if($condition->getOperand() === 'id' && in_array($condition->getOperator(), ['=', 'in'], true)) {
            $has_id_clause = true;
            break 2;
        }
    }
}

if($has_id_clause) {
    $domain->addCondition(new DomainCondition('state', '<>', 'unknown'));
}

// convert sorting comma notation to a map ([order_field => sort_direction, ...])
$sort = [];
$sort_parts  = explode(',', str_replace(' ', '', $params['sort']));
$order_parts = explode(',', str_replace(' ', '', $params['order']));

foreach($order_parts as $index => $order) {
    $order_sort = (count($sort_parts) > $index)? $sort_parts[$index] : $sort_parts[count($sort_parts)-1];
    $sort[$order] = $order_sort;
}

$collection = $params['entity']::search($domain->toArray(), [ 'sort' => $sort ], $params['lang']);

$total = count($collection->ids());

// retrieve list as an array
// #memo - JSON objects handled by ES2015+ might have their keys order altered, so returning result as a map is not safe

if(!$params['nolimit']) {
    $collection
        ->shift($params['start'])
        ->limit($params['limit']);
}

$result = $collection
        ->read($fields, $params['lang'])
        ->adapt('json')
        ->get(true);

$context->httpResponse()
        ->header('X-Total-Count', $total)
        ->body($result)
        ->send();
