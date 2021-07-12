<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Update (fully or partially) the given object.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'id' =>  [
            'description'   => 'Unique identifier of the object to update.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'ids' =>  [
            'description'   => 'List of Unique identifiers of the objects to update.',
            'type'          => 'array',
            'default'       => []
        ],
        'fields' =>  [
            'description'   => 'Associative map of fields to be updated, with their related values.',
            'type'          => 'array', 
            'default'       => []
        ],
        'force' =>  [
            'description'   => 'Flag for forcing update in case a concurrent change is detected.',
            'type'          => 'boolean', 
            'default'       => false
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt'] 
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

if( empty($params['ids']) ) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", QN_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

// adapt received values for parameter 'fields' (which are still text formated)
$schema = $model->getSchema();
foreach($params['fields'] as $field => $value) {
    // drop empty fields, ignore status
    if(is_null($value)) {
        unset($params['fields'][$field]);
    }
    else {
        // adapt received values based on their type (as defined in schema)
        $params['fields'][$field] = $adapter->adapt($value, $schema[$field]['type']);
    }
}


// if we're updating a single object, enforce Optimistic Concurrency Control (https://en.wikipedia.org/wiki/Optimistic_concurrency_control)
if( count($params['ids']) == 1) {
    // handle draft edition
    if(isset($params['fields']['state']) && $params['fields']['state'] == 'draft') {
        $object = $params['entity']::ids($params['ids'])->read(['state'])->first();
        // if state has changed (which means it has been modified by another user in the meanwhile), then we need to create a new object        
        if($object['state'] != 'draft') {
            unset($params['fields']['id']);
            $instance = $params['entity']::create($params['fields'], $params['lang'])->read(['id'])->adapt('txt')->first();
            $params['ids'] = [$instance['id']];
        }
    }
    // handle instances edition
    else if(isset($params['fields']['modified']) ) {
        $object = $params['entity']::ids($params['ids'])->read(['modified'])->first();
        // a changed occured in the meantime
        if($object['modified'] != $params['fields']['modified'] && !$params['force']) {
            throw new Exception("concurrent_change", QN_ERROR_CONFLICT_OBJECT);
        }
    }
}

$result = $params['entity']::ids($params['ids'])
                           // update with received values (with validation - submit `state` if received)
                           ->update($params['fields'], $params['lang'])
                           // by convention, this controller always sets an object as an instance
                           ->update(['state' => 'instance'])
                           // convert to text
                           ->adapt('txt')
                           // return collection as an array
                           ->get(true);

$context->httpResponse()
        ->status(202)
        ->body([])
        ->send();