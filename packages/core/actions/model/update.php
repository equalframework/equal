<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
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
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => ['context', 'orm', 'adapt', 'auth']
]);

/**
 * @var \equal\php\Context                $context
 * @var \equal\orm\ObjectManager          $orm
 * @var \equal\data\DataAdapterProvider   $dap
 * @var \equal\auth\AuthenticationManager $auth
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap, 'auth' => $auth] = $providers;

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

$result = [];

if(empty($params['ids'])) {
    if( !isset($params['id']) || $params['id'] <= 0 ) {
        throw new Exception("object_invalid_id", EQ_ERROR_INVALID_PARAM);
    }
    $params['ids'][] = $params['id'];
}

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

// adapt received values for parameter 'fields' (which are still formatted as text)
$schema = $model->getSchema();

// remove unknown fields
$fields = array_filter($params['fields'], function($field) use ($schema){
            return isset($schema[$field]);
        },
        ARRAY_FILTER_USE_KEY
    );

foreach($fields as $field => $value) {
    $f = $model->getField($field);
    $descriptor = $f->getDescriptor();
    $type = $descriptor['result_type'];
    // drop empty fields : non-string scalar fields with empty string as value are ignored (unless set to null)
    if(!is_array($value) && !strlen(strval($value)) && !in_array($type, ['boolean', 'string', 'text']) && !is_null($value)) {
        unset($fields[$field]);
        continue;
    }
    // empty strings are considered equivalent to null
    if(in_array($type, ['string', 'text']) && !strlen(strval($value))) {
        $fields[$field] = null;
        continue;
    }
    try {
        // adapt received values based on their type (as defined in schema)
        $fields[$field] = $adapter->adaptIn($value, $f->getUsage());
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
    // when updating a single object, enforce Optimistic Concurrency Control (https://en.wikipedia.org/wiki/Optimistic_concurrency_control)
    if(count($params['ids']) == 1) {
        // handle draft edition
        if(isset($fields['state']) && $fields['state'] == 'draft') {
            $object = $params['entity']::ids($params['ids'])->read(['state', 'modifier'])->first(true);
            // if state has changed by another user, then we need to create a new object
            $user_id = $auth->userId();
            if($object['state'] != 'draft' && $object['modifier'] != $user_id) {
                // create object as draft to avoid a missing_mandatory error, and then update it
                $instance = $params['entity']::create(['state' => 'draft'])
                    ->update($fields, $params['lang'])
                    ->read(['id'])
                    ->adapt('json')
                    ->first(true);
                $params['ids'] = [$instance['id']];
            }
        }
        // handle instances edition
        elseif(isset($fields['modified'])) {
            $object = $params['entity']::ids($params['ids'])->read(['modified'])->first(true);
            // a changed occurred in the meantime
            if($object['modified'] != $fields['modified'] && !$params['force']) {
                throw new Exception("concurrent_change", EQ_ERROR_CONFLICT_OBJECT);
            }
        }
    }

    // an object can never be set back to 'draft'
    if(isset($fields['state']) && $fields['state'] == 'draft') {
        $fields['state'] = 'instance';
    }

    $result = $params['entity']::ids($params['ids'])
        // update with received values (with validation)
        ->update($fields, $params['lang'])
        ->read(['id', 'state', 'name', 'modified'])
        ->adapt('json')
        ->get(true);
}

$context->httpResponse()
    ->status(200)
    ->body($result)
    ->send();
