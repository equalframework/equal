<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;
use equal\orm\DomainCondition;
use equal\orm\Operation;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns a list of entities according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
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
        'datasets' => [
            'description'   => 'Array of datasets descriptors.',
            'type'          => 'array',
            'default'       => []
        ],
        'field' => [
            'description'   => 'Field to use for grouping the objects.',
            'type'          => 'string',
            'default'       => 'created'
        ],
        'range_field' => [
            'description'   => 'Date field to use for filtering the objects by range and grouping time ranges.',
            'type'          => 'string'
        ],
        'group_by' => [
            'description'   => 'Method for grouping the results.',
            'type'          => 'string',
            'selection'     => [
                'field',
                'range'
            ],
            'default'       => 'range'
        ],
        'range_from' => [
            'description'   => 'Start of date range.',
            'type'          => 'date',
            'default'       => function () { return time() - (30 * 86400); }
        ],
        'range_to' => [
            'description'   => 'End of date range.',
            'type'          => 'date',
            'default'       => function () { return time() + (30 * 86400); }
        ],
        'range_interval' => [
            'description'   => 'Time interval for grouping abscissa values.',
            'type'          => 'string',
            'selection'     => [
                'day',
                'week',
                'month',
                'year'
            ],
            'default'       => 'month'
        ],
        'mode' => [
            'description'   => 'Mode defining to way data are to be returned.',
            'type'          => 'string',
            'selection'     => [
                'chart',
                'grid'
            ],
            'default'       => 'chart'
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*',
        'cacheable'     => true,
        'cache-vary'    => ['uri'],
        'expires'       => 30 * (60*60*24)
    ],
    'providers'     => [ 'context', 'orm' ]
]);

['context' => $context, 'orm' => $orm] = $providers;

$getDateIndex = function($date, $interval) {
    switch($interval) {
        case 'day':
            return date('Y-m-d', $date);
        case 'week':
            return date('Y-W', $date);
        case 'month':
            return date('Y-m', $date);
        case 'year':
        default:
            return date('Y', $date);
    }
};

$getNextDate = function($date, $interval) {
    switch($interval) {
        case 'day':
            return strtotime(date('Y-m-d', $date).' +1 day');
        case 'week':
            $date = strtotime(date('Y-m-d', $date));
            $day = (int) date('N', $date);
            return $date + ((8 - $day) * 24 * 3600);
        case 'month':
            return strtotime(date('Y-m-01', $date).' +1 month');
        case 'year':
            return strtotime((date('Y', $date) + 1).'-01-01');
        default:
            return strtotime(date('Y-m-d', $date).' +1 day');
    }
};

// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

/*
Configuration of a view with a list of datasets

The chart layout depends on the selected chart type

LINE, BAR : several datasets, each dataset corresponds to a value for a specific interval : group_by range
POLAR, DOUGHNUT, PIE : a single dataset, each value corresponds to a segment => group_by field: labels are the groups values, values are the operations on groups

    group_by = 'field' or 'range'

                            field                       range
                            -----                       -----
    field                   field on which to group     /
    range_field             field of type 'date'        field of type 'date' to use for grouping (defaults to field)
    range_interval          /                           day, week, month, year
    range_from              date range start            date range start
    range_to                date range end              date range end

Examples:
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=range&range_from=2022-03-01&range_to=2022-12-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=field&range_field=created&range_from=2022-03-01&range_to=2022-06-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]&field=type_id

*/



/*
$datasets = [
    [
        "operation" => ["SUM", "object.total"]
    ],
    [
        "label"     => "Average price for general public"
        "operation" => ["AVG", "object.price"],
        "domain"    => ["type", "=", "general"]
    ]
];
*/

$datasets = $params['datasets'];
$schema = $entity->getSchema();

if(!isset($schema[$params['field']])) {
    throw new Exception("unknown_field", EQ_ERROR_INVALID_PARAM);
}

$group_field_descriptor = $schema[$params['field']];
$group_field_type = $group_field_descriptor['result_type'] ?? $group_field_descriptor['type'];
$is_group_field_many2one = ($group_field_type == 'many2one' && isset($group_field_descriptor['foreign_object']));

$range_field = $params['range_field'] ?? (($params['group_by'] == 'range') ? $params['field'] : null);
$range_field_type = null;

if(!is_null($range_field)) {
    if(!isset($schema[$range_field])) {
        throw new Exception("unknown_range_field", EQ_ERROR_INVALID_PARAM);
    }

    $range_field_descriptor = $schema[$range_field];
    $range_field_type = $range_field_descriptor['result_type'] ?? $range_field_descriptor['type'];

    if(!in_array($range_field_type, ['date', 'datetime'])) {
        throw new Exception("invalid_range_field", EQ_ERROR_INVALID_PARAM);
    }
}

$fields = [];

$str = json_encode($datasets);
$re = '/"object\.(.*)"/U';

preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

$fields = array_values(array_unique(array_map(function($a) {return $a[1];}, $matches)));

// make sure grouping field is amongst requested fields
if($is_group_field_many2one) {
    $fields = array_values(array_filter($fields, fn($field) => $field !== $params['field']));
    $fields[$params['field']] = ['id', 'name'];
}
else if(!in_array($params['field'], $fields)) {
    $fields[] = $params['field'];
}

if(!is_null($range_field) && !in_array($range_field, $fields)) {
    $fields[] = $range_field;
}

// add clause related to time range
$domain = new Domain($params['domain']);

if(!is_null($range_field)) {
    $range_to_operator = ($range_field_type == 'datetime') ? '<' : '<=';
    $range_to = ($range_field_type == 'datetime') ? $getNextDate($params['range_to'], 'day') : $params['range_to'];

    $domain->addCondition(new DomainCondition($range_field, '>=', $params['range_from']))
           ->addCondition(new DomainCondition($range_field, $range_to_operator, $range_to));
}

// init results_map as an empty associative array of intervals map
$results_map = [];

if($params['group_by'] == 'range') {
    $date = $params['range_from'];
    while($date <= $params['range_to']) {
        $index = $getDateIndex($date, $params['range_interval']);
        $results_map[$index] = [];
        $date = $getNextDate($date, $params['range_interval']);
    }
}
else {
    // $datasets = [$datasets[0]];
}

// populate final result array with operations results
$result = array_fill_keys(array_keys($results_map), []);

// final array of dataset legends
$legends = [];
// working map for group labels
$labels_map = [];

foreach($datasets as $index => $dataset) {
    $operation = $dataset['operation'];

    $legends[$index] = (isset($dataset['label'])) ? $dataset['label'] : '#value';

    $op = new Operation($operation);

    $dom = new Domain($domain->toArray());
    if(isset($dataset['domain'])) {
        $dom = $dom->merge(new Domain($dataset['domain']));
    }

    // init empty intervals map
    $result_map = $results_map;
    // search objects matching given domain and date range
    $objects = $params['entity']::search($dom->toArray())->read($fields)->get();
    if($objects && count($objects)) {
        // group objects by date interval
        foreach($objects as $oid => $object) {
            if($params['group_by'] == 'range') {
                $group_index = $getDateIndex($object[$range_field], $params['range_interval']);
                $group_label = $group_index;
            }
            elseif(in_array($group_field_type, ['date', 'datetime'])) {
                // #todo - check value of param 1
                $group_index = date('Y-m-d', $object[$params['field']]);
                $group_label = $group_index;
            }
            elseif($is_group_field_many2one) {
                $group_index = $object[$params['field']]['id'] ?? null;
                $group_label = $object[$params['field']]['name'] ?? $group_index;
            }
            else {
                $group_index = $object[$params['field']];
                $group_label = $group_index;
            }

            if(!isset($labels_map[$group_index])) {
                $labels_map[$group_index] = strval($group_label);
            }
            $result_map[$group_index][] = $object;
        }
        foreach($result_map as $group_index => $objects) {
            $result[$group_index][$index] = round($op->compute($objects), 2);
        }
    }
}

$datasets = [];
$labels = [];
foreach($result as $date_index => $sets) {
    foreach(array_keys($legends) as $index) {
        if(!isset($datasets[$index])) {
            $datasets[$index] = [];
        }
        $datasets[$index][] = array_key_exists($index, $sets) ? $sets[$index] : null;
    }
    if($params['group_by'] == 'field') {
        $labels[] = $labels_map[$date_index] ?? strval($date_index);
    }
}

if($params['group_by'] === 'range') {
    $result = [
        'labels'    => array_keys($results_map),
        'datasets'  => array_values($datasets),
        'legends'   => array_values($legends)
    ];
}
else if($params['group_by'] === 'field') {
    $result = [
        'labels'    => array_values($labels),
        'datasets'  => array_values($datasets),
        'legends'   => array_values($legends)
    ];
}

if($params['mode'] === 'grid') {
    $res = [];

    if($params['group_by'] == 'range') {
        $keys = array_merge(['#label'], $result['labels']);
        foreach($result['datasets'] as $i => $dataset) {
            $values =  array_merge( [$result['legends'][$i]], $dataset);
            $res[] = array_combine($keys, $values);
        }
    }
    else if($params['group_by'] == 'field') {
        $keys = array_merge(['#label'], $result['labels']);
        foreach($result['datasets'] as $i => $dataset) {
            $values =  array_merge( [$result['legends'][$i]], $dataset);
            $res[] = array_combine($keys, $values);
        }
    }

    $result = &$res;
}

$context->httpResponse()
        ->body($result)
        ->send();
