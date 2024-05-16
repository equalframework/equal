<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the sum of two values.',
    'params'        => [
        'first_value' => [
            'description'   => 'First value',
            'type'          => 'integer',
            'usage'     => 'numeric/integer',
            'required'      => true
        ],
        'second_value' => [
            'description'   => 'Second value',
            'type'          => 'integer',
            'usage'     => 'numeric/integer',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*',
        'schema' => [
            'type'          => 'integer',
            'usage'     => 'numeric/integer',
            'qty'       => 'one'
        ]
    ],
    'access'        => [
        'visibility'        => 'public',
    ],
    'providers'     => ['context']
]);

list($context) = [$providers['context']];

$result = $params['first_value'] + $params['second_value'];

$context->httpResponse()
    ->body($result)
    ->send();
