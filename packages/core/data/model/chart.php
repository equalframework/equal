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
            'required'      => true
        ],
        'range_to' => [
            'description'   => 'End of date range.',
            'type'          => 'date',
            'required'      => true
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


/* 
examples: 
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=range&range_from=2022-03-01&range_to=2022-06-30&operations={test:{total:{operation:SUM}}}
    /?get=model_chart&entity=lodging\sale\booking\Booking&group_by=field&range_from=2022-03-01&range_to=2022-06-30&operations={test:{total:{operation:SUM}}}&field=type
*/

if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
}

/*
configuration d'une vue avec la liste des datasets

la configuration du chart dépend du type de chart sélectionné

LINE, BAR : plusieurs datasets, chaque dataset correspond à une valeur pour un interval donné : group_by range
POLAR, DOUGHNUT, PIE : 1 seul dataset, chaque valeur correspond à un segment => group_by field: les labels sont les valeurs de groupes, les valeurs sont les operations sur les groupes
    
    group_by = field, range

                            field                       range
    field                   field on which to group     field holding the dates for grouping
    range_interval          /                           week, month, year
    range_from
    range_to


*/



/*
$datasets = [
    [
        "operation" => ["SUM", "object.total"]
    ],
    [
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
    $datasets = [$datasets[0]];
}


// populate final result array with operations results
$result = array_fill_keys(array_keys($results_map), []);

foreach($datasets as $index => $dataset) {
    $operation = $dataset['operation'];
    
    $op = new Operation($operation);

    $dom = new Domain($domain->toArray());
    if(isset($dataset['domain'])) {
        $dom = $dom->merge(new Domain($dataset['domain']));
    }

    // reset intervals map
    $results_map = array_fill_keys(array_keys($results_map), []);
    // search objects matching given domain and date range
    $objects = $params['entity']::search($dom->toArray())->read($fields)->get();

    if($objects && count($objects)) {
        // group objects by date interval
        foreach($objects as $oid => $object) {
            if($params['group_by'] == 'range') {
                $group_index = _get_date_index($object[$params['field']], $params['range_interval']);
            }
            else {
                $group_index = $object[$params['field']];
            }
            $results_map[$group_index][] = $object;
        }
        foreach($results_map as $group_index => $objects) {
            $result[$group_index][$index] = $op->compute($objects);
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
    }
}

$context->httpResponse()
        ->body([
            'labels'    => array_keys($results_map),
            'datasets'  => $datasets
        ])
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