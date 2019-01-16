<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the list of classes defined in specified package',
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


$types = [
    'alias'  => [
        'alias' => ['type' => 'select_field', 'origin' => 'self']
    ],

    'boolean'   => [],    
    'integer'   => [],
    'float'     => [],    
    'string'    => [
        'multilang'   => ['type' => 'boolean']    
    ],
    'text'      => [
        'multilang'   => ['type' => 'boolean']           
    ],
    'html'      => [
        'multilang'   => ['type' => 'boolean']       
    ],
    'date'      => [],
    'time'      => [],
    'datetime'  => [],
    'file'      => [],
    'binary'    => [],    

    'many2one'  => [
        'foreign_object' => ['type' => 'select_class']
    ],

    'one2many'  => [
        'foreign_object' => ['type' => 'select_class'],
        'foreign_field'  => ['type' => 'select_field', 'origin' => 'selected']
    ],    

    'many2many' => [
        'foreign_object' => ['type' => 'select_class'],
        'foreign_field'  => ['type' => 'select_field', 'origin' => 'selected'],
    ],
    
    'function'  => [
        'result_type' => ['type' => 'select', 'select' => $orm::$simple_types],
        'store'       => ['type' => 'boolean'],
        'multilang'   => ['type' => 'boolean']        
    ]
];
$context->httpResponse()
        ->body($types)
        ->send();