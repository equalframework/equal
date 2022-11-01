<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Transform an object being edited in a view, according to the onchange method of the entity, if any.",
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
        'changes' =>  [
            'description'   => 'Associative array holding the fields whose values have been changed, with their related values.',
            'type'          => 'array',
            'default'       => []
        ],
        'values' =>  [
            'description'   => 'Copy of the (partial) object being edited with original value.',
            'type'          => 'array',
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'access'        => [
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

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

// adapt received values for parameter 'values' and 'changes' (which are still formated as text)
$schema = $model->getSchema();

// keep only known and non-empty fields (allow null values)

$values = array_filter($params['values'], function($val, $field) use ($schema){
    return (is_array($val) || strlen(strval($val)) || is_null($val) || in_array($schema[$field]['type'], ['boolean', 'string', 'text']) ) && isset($schema[$field]);
}, ARRAY_FILTER_USE_BOTH);

$changes = array_filter($params['changes'], function($val, $field) use ($schema){
    return (is_array($val) || strlen(strval($val)) || is_null($val) || in_array($schema[$field]['type'], ['boolean', 'string', 'text']) ) && isset($schema[$field]);
}, ARRAY_FILTER_USE_BOTH);


// adapt fields in values array
foreach($values as $field => $value) {
    $type = $schema[$field]['type'];
    if($type == 'computed') {
        $type = $schema[$field]['result_type'];
    }
    try {
        // adapt received values based on their type (as defined in schema)
        $values[$field] = $adapter->adapt($value, $type);
    }
    catch(Exception $e) {
        // ignore invalid fields
        unset($values[$field]);
    }
}
// adapt fields in changes array
foreach($changes as $field => $value) {
    $type = $schema[$field]['type'];
    if($type == 'computed') {
        $type = $schema[$field]['result_type'];
    }
    try {
        // adapt received values based on their type (as defined in schema)
        $changes[$field] = $adapter->adapt($value, $type);
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


$result = $model::onchange($orm, $changes, $values, $params['lang']);

// adapt resulting values to json
foreach($result as $field => $value) {
    $type = $schema[$field]['type'];
    if($type == 'computed') {
        $type = $schema[$field]['result_type'];
    }
    // adapt received values based on their type (as defined in schema)
    $result[$field] = $adapter->adapt($value, $type, 'txt', 'php');
}

$context->httpResponse()
        ->status(200)
        ->body($result)
        ->send();