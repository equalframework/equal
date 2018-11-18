<?php

$params = eQual::announce([
    'description'   => 'This ia an example to show how to use the announce() method',
    'params' => [
        'test'  => [
            'description'   => 'This is a mandatory test argument that has to be formated as an integer',x
            'type'          => 'integer',
            'required'      => true
         ]
    ]
]);

print_r($params);