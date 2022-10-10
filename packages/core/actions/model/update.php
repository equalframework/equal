<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Update (fully or partially) the given object.",
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
            'description'   => 'Associative array mapping fields to be updated with their related values.',
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
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 * @var \equal\data\DataAdapter     $adapter
 */
list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];
$result = [];

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

// adapt received values for parameter 'fields' (which are still formated as text)
$schema = $model->getSchema();
// remove unknown fields
$fields = array_filter($params['fields'], function($field) use ($schema){
    return isset($schema[$field]);
}, ARRAY_FILTER_USE_KEY);


foreach($fields as $field => $value) {
    $type = $schema[$field]['type'];
    // drop empty fields (allow reset to null)
    if(!is_array($value) && !strlen(strval($value)) && !in_array($type, ['boolean', 'string', 'text']) && !is_null($value) ) {
        unset($fields[$field]);
        continue;
    }
    if($type == 'computed') {
        $type = $schema[$field]['result_type'];
    }
    try {
        // adapt received values based on their type (as defined in schema)
        $fields[$field] = $adapter->adapt($value, $type);
    }
    catch(Exception $e) {
        $msg = $e->getMessage();
        // handle serialized objects as message
        $data = @unserialize($msg);
        if ($data !== false) {
            $msg = $data;
        }
        throw new \Exception(serialize([$field => $msg]), $e->getCode());
    }
}


if(count($fields)) {
    // we're updating a single object: enforce Optimistic Concurrency Control (https://en.wikipedia.org/wiki/Optimistic_concurrency_control)
    if( count($params['ids']) == 1) {
        // handle draft edition
        if(isset($fields['state']) && $fields['state'] == 'draft') {
            $object = $params['entity']::ids($params['ids'])->read(['state'])->first(true);
            // if state has changed (which means it has been modified by another user in the meanwhile), then we need to create a new object
            if($object['state'] != 'draft') {
                // create object as draft to avoid a missing_mandatory error, and then update it
                $instance = $params['entity']::create(['state' => 'draft'])
                                             ->update($fields, $params['lang'])
                                             ->read(['id'])
                                             ->adapt('txt')
                                             ->first(true);
                $params['ids'] = [$instance['id']];
            }
        }
        // handle instances edition
        else if(isset($fields['modified']) ) {
            $object = $params['entity']::ids($params['ids'])->read(['modified'])->first(true);
            // a changed occured in the meantime
            if($object['modified'] != $fields['modified'] && !$params['force']) {
                throw new Exception("concurrent_change", QN_ERROR_CONFLICT_OBJECT);
            }
        }
    }

    $result = $params['entity']::ids($params['ids'])
                            // update with received values (with validation - submit `state` if received)
                            ->update($fields, $params['lang'])
                            ->read(['id', 'state', 'name', 'modified'])
                            ->adapt('txt')
                            ->get(true);
}

$context->httpResponse()
        ->status(200)
        ->body($result)
        ->send();