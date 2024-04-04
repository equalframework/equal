<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Field;
use equal\orm\Collections;

list($params, $providers) = eQual::announce([
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
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $orm, $dap) = [$providers['context'], $providers['orm'], $providers['adapt']];

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

$result = [];

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

if(method_exists($params['entity'], 'onchange')) {
    // adapt received values for parameter 'values' and 'changes' (which are still formatted as text)
    $schema = $model->getSchema();

    // keep only known and non-empty fields (allow null values)

    $values = array_filter($params['values'], function($val, $field) use ($schema){
            return (is_array($val) || strlen(strval($val)) || is_null($val) || in_array($schema[$field]['type'], ['boolean', 'string', 'text']) ) && isset($schema[$field]);
        }, ARRAY_FILTER_USE_BOTH);

    $changes = array_filter($params['changes'], function($val, $field) use ($schema){
            return (is_array($val) || strlen(strval($val)) || is_null($val) || in_array($schema[$field]['type'], ['boolean', 'string', 'text']) ) && isset($schema[$field]);
        }, ARRAY_FILTER_USE_BOTH);

    // adapt fields in $values array
    foreach($values as $field => $value) {
        try {
            /** @var equal\orm\Field */
            $f = $model->getField($field);
            // adapt received values based on their type (as defined in schema)
            $values[$field] = $adapter->adaptIn($value, $f->getUsage());
        }
        catch(Exception $e) {
            // ignore invalid fields
            unset($values[$field]);
        }
    }

    // adapt fields in $changes array
    foreach($changes as $field => $value) {
        try {
            $f = new Field($schema[$field]);
            // adapt received values based on their type (as defined in schema)
            $changes[$field] = $adapter->adaptIn($value, $f->getUsage());
        }
        catch(Exception $e) {
            $msg = $e->getMessage();
            // handle serialized objects as message
            $data = @unserialize($msg);
            if ($data !== false) {
                $msg = $data;
            }
            throw new Exception(serialize([$field => $msg]), $e->getCode());
        }
    }

    // inject parameters based on the target method signature

    /** @var ReflectionMethod */
    $method = new ReflectionMethod($params['entity'], 'onchange');
    /** @var ReflectionParameter */
    $parameters = $method->getParameters();

    $args = [];
    foreach($parameters as $param) {
        $param_name = $param->getName();
        if(in_array($param_name, ['om', 'orm'])) {
            $args[] = $orm;
        }
        elseif(in_array($param_name, ['changes', 'event'])) {
            $args[] = $changes;
        }
        elseif($param_name == 'values') {
            $args[] = $values;
        }
        elseif($param_name == 'lang') {
            $args[] = $lang;
        }
        elseif($param_name == 'self') {
            // #todo - should we add object id to the params ?
            $factory = Collections::getInstance();
            $c = $factory->create($params['entity']);
            $c->lang($params['lang']);
            $args[] = $c;
        }
    }

    $result = $params['entity']::onchange(...$args);

    // adapt resulting values to json
    foreach($result as $field => $value) {
        // leave array untouched (not an ORM type)
        if(is_array($value)) {
            continue;
        }
        // convert objects to arrays (for supporting values retrieved as sub-objects)
        if(is_object($value)) {
            if(is_subclass_of($value, 'equal\orm\Model')) {
                $result[$field] = $value->toArray();
            }
            else {
                $result[$field] = serialize($value);
            }
        }
        else {
            $f = new Field($schema[$field]);
            // adapt received values based on their type (as defined in schema)
            $result[$field] = $adapter->adaptOut($value, $f->getUsage());
        }
    }

}

$context->httpResponse()
        ->body($result)
        ->send();
