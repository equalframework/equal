<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
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
        'id' =>  [
            'description'   => 'Unique identifier of the object being edited.',
            'type'          => 'integer',
            'default'       => 0
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'form.default'
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
            $f = $model->getField($field);
            // #memo - for binary fields meta data are relayed instead of binary data
            if($f->getDescriptor()['result_type'] !== 'binary') {
                // adapt received values based on their type (as defined in schema)
                $changes[$field] = $adapter->adaptIn($value, $f->getUsage());
            }
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

    // use the current object id so uniqueness validation does not conflict with the object itself
    $object_id = (int) ($params['id'] ?: ($values['id'] ?? 0));
    $self = $params['entity']::ids($object_id > 0 ? [$object_id] : [])->lang($params['lang']);

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
            $args[] = $params['lang'];
        }
        elseif($param_name == 'view') {
            $args[] = $params['view_id'];
        }
        elseif($param_name == 'self') {
            $args[] = $self;
        }
    }

    $result = $params['entity']::onchange(...$args);

    $ui_properties = array_flip(['value', 'domain', 'selection', 'visible', 'readonly', 'required', 'errors']);
    $isUiDescriptor = static fn($value) => is_array($value) && array_intersect_key($value, $ui_properties);

    // validate only values explicitly returned by onchange; UI-only descriptors without a value are ignored
    $validation_values = [];
    foreach($result as $field => $field_result) {
        $is_descriptor = $isUiDescriptor($field_result);
        if($is_descriptor && !array_key_exists('value', $field_result)) {
            continue;
        }

        $f = $model->getField($field);
        if(!$f) {
            continue;
        }
        $value = $is_descriptor ? $field_result['value'] : $field_result;
        $result_type = $f->getDescriptor()['result_type'];

        // ORM validation expects relation identifiers, while onchange may return ORM objects for the UI
        if($value instanceof \equal\orm\Model) {
            $value = $value->toArray();
        }
        elseif($value instanceof \equal\orm\Collection) {
            $value = $value->ids();
        }
        elseif(is_object($value)) {
            continue;
        }

        if($result_type === 'many2one' && is_array($value) && array_key_exists('id', $value)) {
            $value = $value['id'];
        }

        // optional empty strings are equivalent to null for ORM validation, but remain unchanged in the UI result
        if($value === '' && in_array($result_type, ['string', 'text'])) {
            $value = null;
        }

        $validation_values[$field] = $value;
    }

    if($validation_values) {
        $errors = $orm->validate($params['entity'], $self->ids(), $validation_values, true);
        foreach(reset($errors) ?: [] as $field => $field_errors) {
            if(!$isUiDescriptor($result[$field])) {
                $result[$field] = ['value' => $result[$field]];
            }
            $result[$field]['errors'] = (object) array_merge((array) ($result[$field]['errors'] ?? []), $field_errors);
        }
    }

    // adapt direct values and descriptor values to json
    foreach($result as $field => &$field_result) {
        $is_descriptor = $isUiDescriptor($field_result);
        if(($is_descriptor && !array_key_exists('value', $field_result)) || (!$is_descriptor && is_array($field_result))) {
            continue;
        }
        $value = $is_descriptor ? $field_result['value'] : $field_result;
        if(is_array($value)) {
            continue;
        }
        if($value instanceof \equal\orm\Model) {
            $value = $value->toArray();
        }
        elseif(is_object($value)) {
            $value = serialize($value);
        }
        elseif($f = $model->getField($field)) {
            $value = $adapter->adaptOut($value, $f->getUsage());
        }
        else {
            trigger_error('APP::invalid field '.$field, EQ_REPORT_WARNING);
        }

        $field_result = $is_descriptor ? array_merge($field_result, ['value' => $value]) : $value;
    }
    unset($field_result);

}

$context->httpResponse()
        ->body($result)
        ->send();
