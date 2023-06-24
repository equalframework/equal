<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns schema of available types and related possible attributes.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'params'        => [
    ],
    'providers'     => ['context', 'orm']
]);


list($context, $orm) = [ $providers['context'], $providers['orm'] ];


// $types = array_merge($orm::$simple_types, $orm::$complex_types);
$default_descriptor = [
    'description'           => ['type' => 'string', 'description' => 'Brief about the field.'],
    'help'                  => ['type' => 'string', 'description' => 'Additional information about the field.'],
    'visible'               => ['type' => 'domain', 'description' => 'Domain holding the conditions that must be met in order for the field to be relevant.'],
    'readonly'              => ['type' => 'boolean', 'description' => 'Marks the field as non-editable'],
    'required'              => ['type' => 'boolean', 'description' => 'Marks the field as mandatory']
];


$properties_descriptor = [
    'usage'             => ['type' => 'string', 'description' => 'Specifies additional information about the format of the field.'],
    'onupdate'          => ['type' => 'string', 'description' => 'Name of the method to invoke when field is updated.'],
    'dependencies'      => ['type' => 'array', 'description' => 'List of computed fields that must be reset when the value of the field is updated.'],
    'selection'         => ['type' => 'array', 'description' => 'Pre-defined list or associative array holding the possible values for the field.'],
    'unique'            => ['type' => 'boolean', 'description' => 'If the property need to be unique.'],
    'precision'         => ['type' => 'integer', 'description' => 'Precision behind the point for a float.'],
    'multilang'         => ['type' => 'boolean', 'description' => 'Marks the field as translatable'],
    'foreign_object'    => ['type' => 'select_class', 'description' => 'Full name of the class toward which field is pointing back.'],
    'foreign_field'     => ['type' => 'select_field', 'origin' => 'foreign_object', 'description' => 'Name of the field of the pointed class that is pointing back toward the current class.'],
    'domain'            => ['type' => 'domain', 'description' => ' Domain holding the additional conditions to apply on the set of objects targeted by the relation.'],
    'ondelete'          => ['type' => 'string', 'description' => 'Tells how to behave when the parent object is deleted.'],
    'ondetach'          => ['type' => 'string', 'description' => 'Tells how to handle the children objects when the relation is removed: either set the pointer to null or delete the children objects.'],
    'order'             => ['type' => 'select_field', 'origin' => 'self' , 'description' => 'Name of the field pointed objects must be sorted on.'],
    'sort'              => ['type' => 'string', 'selection' => ['asc', 'desc'], 'description' => 'Direction fort sorting'],
    'rel_table'         => ['type' => 'string', 'description' => 'Name of the SQL table dedicated to the m2m relation.'],
    'rel_local_key'     => ['type' => 'string', 'description' => 'Name of the column in rel_table holding the identifier of the current object.'],
    'rel_foreign_key'   => ['type' => 'string', 'description' => 'Name of the column in de rel_table holding the identifier of the pointed object.'],
    'result_type'       => ['type' => 'string', 'selection' => array_merge($orm::$simple_types, array('one2many', 'many2many')), 'description' =>'Specifies the type of the result returned by the field function, which can be any of the allowed types.'],
    'function'          => ['type' => 'string', 'description' => 'String holding the name of the method to invoke for computing the value of the field.'],
    'store'             => ['type' => 'boolean', 'description' =>'Tell if the result must be stored in database.'],
    'instant'           => ['type' => 'boolean', 'description' =>'Tell if the property need to be instantly recalculated if null in DB.'],
];

$types = [
    'alias'     => [
        'type'              => ['type' => 'alias'],
        'alias'             => ['type' => 'select_field', 'origin' => 'self', 'description' => 'Targets another field whose value must be returned when fetching the field value.'],
        'usage'
    ],

    'boolean'   => [
        'type'              => ['type' => 'boolean'],
        'default'           => ['type' => 'boolean'],
        'dependencies', 'onupdate', 'usage'
    ],

    'integer'   => [
        'type'              => ['type' => 'integer'],
        'default'           => ['type' => 'integer'],
        'dependencies', 'onupdate', 'selection', 'unique', 'usage'
    ],

    'float'     => [
        'type'              => ['type' => 'float'],
        'default'           => ['type' => 'float'],
        'dependencies', 'onupdate', 'selection', 'precision', 'usage'
    ],

    'string'    => [
        'type'              => ['type' => 'string'],
        'default'           => ['type' => 'string'],
        'dependencies', 'onupdate', 'selection', 'multilang', 'unique', 'usage'
    ],

    'text'      => [
        'type'              => ['type' => 'text'],
        'default'           => ['type' => 'text'],
        'dependencies', 'onupdate', 'multilang', 'usage'
    ],

    'date'      => [
        'type'              => ['type' => 'date'],
        'default'           => ['type' => 'date'],
        'dependencies', 'onupdate', 'usage'
    ],

    'time'      => [
        'type'              => ['type' => 'time'],
        'default'           => ['type' => 'time'],
        'dependencies', 'onupdate', 'usage'
    ],

    'datetime'  => [
        'type'              => ['type' => 'datetime'],
        'default'           => ['type' => 'datetime'],
        'dependencies', 'onupdate', 'usage'
    ],

    'binary'    => [
        'type'              => ['type' => 'binary'],
        'default'           => ['type' => 'binary'],
        'dependencies', 'onupdate', 'multilang', 'usage'
    ],

    'many2one'  => [
        'type'              => ['type' => 'string'],
        'default'           => ['type' => 'string'],
        'dependencies', 'foreign_object', 'domain', 'onupdate', 'ondelete', 'multilang'
    ],

    'one2many'  => [
        'type'              => ['type' => 'string'],
        'default'           => ['type' => 'string'],
        'dependencies', 'foreign_object', 'foreign_field', 'domain', 'onupdate', 'ondelete','ondetach', 'order', 'sort'
    ],

    'many2many' => [
        'type'              => ['type' => 'string'],
        'default'           => ['type' => 'string'],
        'dependencies', 'foreign_object', 'foreign_field','rel_table', 'rel_local_key','rel_foreign_key', 'domain', 'onupdate'
    ],

    'computed'  => [
        'type'              => ['type' => 'string'],
        //'default'           => ['type' => 'string'],
        'function', 'result_type', 'onupdate', 'store', 'instant', 'multilang', 'selection', 'foreign_object'
    ]
];

$result = [];
foreach($types as $type => $descriptor) {
    $result[$type] = $default_descriptor;
    foreach($descriptor as $property => $value) {
        if(is_numeric($property)) {
            $property = $value;
            $result[$type][$property] = $properties_descriptor[$property];
        }
        else {
            $result[$type][$property] = $value;
        }
    }
}

$context->httpResponse()
        ->body($result)
        ->send();
