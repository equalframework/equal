<?php

$params = eQual::announce([
    'description'   => 'This ia an example to show how to use the announce() method',
    'params' => [
        'a'  => [
            'description'   => 'This is a mandatory argument that has to be formated as an integer',
            'type'          => 'integer',
            'required'      => true
         ],
        'b'  => [
            'description'   => 'This is an optional string argument',
            'type'          => 'string'
         ],
        'c'  => [
            'description'   => 'This is an optional argument with a default value (will always be present)',
            'type'          => 'string',
            'default'       => 'test'
         ]
         
    ]
]);

print_r($params);