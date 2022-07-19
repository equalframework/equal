<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Domain;
use equal\orm\DomainCondition;
use equal\orm\Operation;

list($params, $providers) = announce([
    'description'   => 'Returns a list of entites according to given domain (filter), start offset, limit and order.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'lang' =>  [
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string',
            'default'       => DEFAULT_LANG
        ],
        'domain' => [
            'description'   => 'Criterias that results have to match (serie of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ],
        'datasets' => [
            'description'   => 'Array of datasets descriptors.',
            'type'          => 'array',
            'default'       => []
        ],
        'field' => [
            'description'   => 'Field to use for grouping the objects (date field in case of a time range).',
            'type'          => 'string',
            'default'       => 'created'
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
            'default'       => time() - (30 * 86400)
        ],
        'range_to' => [
            'description'   => 'End of date range.',
            'type'          => 'date',
            'default'       => time() + (30 * 86400)
        ],
        'range_interval' => [
            'description'   => 'Time interval for grouping absciss values.',
            'type'          => 'string',
            'selection'     => [
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
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

list($context) = [ $providers['context'] ];



if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
}

/*
Configuration of a view with a list of datasets

The chart layout depends on the selected chart type

LINE, BAR : plusieurs datasets, chaque dataset correspond à une valeur pour un interval donné : group_by range
POLAR, DOUGHNUT, PIE : 1 seul dataset, chaque valeur correspond à un segment => group_by field: les labels sont les valeurs de groupes, les valeurs sont les operations sur les groupes

    group_by = 'field' or 'range'

                            field                       range
                            -----                       -----
    field                   field on which to group     field of type 'date' to use for grouping (defaults to 'created')
    range_interval          /                           week, month, year
    range_from              /
    range_to                /

Examples:
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=range&range_from=2022-03-01&range_to=2022-12-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=field&range_from=2022-03-01&range_to=2022-06-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]&field=type_id

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

$fields = [];

$str = json_encode($datasets);
$re = '/"object\.(.*)"/U';

preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);

$fields = array_map(function($a) {return $a[1];}, $matches);

// make sure grouping field is amongst requested fields
$fields[] = $params['field'];

// add clause related to time range
$domain = new Domain($params['domain']);

if($params['group_by'] == 'range') {
    $domain->addCondition(new DomainCondition($params['field'], '>=', $params['range_from']))
           ->addCondition(new DomainCondition($params['field'], '<=', $params['range_to']));
}

// initilize results_map as an empty associative array of intervals map
$results_map = [];

if($params['group_by'] == 'range') {
    $date = $params['range_from'];
    while($date < $params['range_to']) {
        $index = _get_date_index($date, $params['range_interval']);
        $results_map[$index] = [];
        $date = _get_next_date($date, $params['range_interval']);
    }
}
else {
    // $datasets = [$datasets[0]];
}

// populate final result array with operations results
$result = array_fill_keys(array_keys($results_map), []);

// final array of legends (field)
$labels = [];
// final array of legends (range)
$legends = [];
// working map for legends
$legends_map = [];

foreach($datasets as $index => $dataset) {
    $operation = $dataset['operation'];

    if($params['group_by'] == 'range') {
        $legends_map[$index] = (isset($dataset['label']))?$dataset['label']:'';
    }
    else {
        $labels[] = (isset($dataset['label']))?$dataset['label']:'#value';
    }

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
                $group_index = _get_date_index($object[$params['field']], $params['range_interval']);
            }
            else {
                // #todo - check value of param 1
                $group_index = date('Y-m-d', $object[$params['field']]);
            }
            $result_map[$group_index][] = $object;
        }
        foreach($result_map as $group_index => $objects) {
            $result[$group_index][$index] = round($op->compute($objects), 2);
        }
    }
}

$datasets = [];
foreach($result as $date_index => $sets) {
    foreach($sets as $index => $value) {
        if(!isset($datasets[$index])) {
            $datasets[$index] = [];
        }
        $datasets[$index][] = $value;
        if($params['group_by'] == 'range' && !isset($legends[$index])) {
            $legends[$index] = $legends_map[$index];
        }
    }
    if($params['group_by'] == 'field') {
        $legends[] = strval($date_index);
    }
}

if($params['group_by'] == 'range') {
    $result = [
        'labels'    => array_keys($results_map),
        'datasets'  => array_values($datasets),
        'legends'   => array_values($legends)
    ];
}
else if($params['group_by'] == 'field') {
    $result = [
        'labels'    => $labels,
        'datasets'  => array_values($datasets),
        'legends'   => array_values($legends)
    ];
}

if($params['mode'] == 'grid') {
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
        foreach($result['legends'] as $i => $legend) {
            $values = [$legend];
            foreach($result['datasets'] as $dataset) {
                $values[] = $dataset[$i];
            }
            $res[] = array_combine($keys, $values);            
        }
    }

    $result = &$res;
}

$context->httpResponse()
        ->body($result)
        ->send();


function _get_date_index($date, $interval) {
    switch($interval) {
        case 'week':
            return date('Y-W', $date);
        case 'month':
            return date('Y-m', $date);
        case 'year':
            return date('Y', $date);
    }
}

function _get_next_date($date, $interval) {
    switch($interval) {
        case 'week':
            $day = date("w", $date);
            $day = ($day == 0)?7:$day;
            return $date + ((8-$day)*24*3600);
        case 'month':
            return strtotime(date("Y-m-t", $date)) + (48*3600);
        case 'year':
            return strtotime( (date('Y', $date) + 1).'-01-02');
    }
}