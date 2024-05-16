<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the division of two values.',
    'params'        => [
        'numerator' => [
            'description'   => 'Numerator',
            'type'          => 'integer',
            'usage'         => 'numeric/integer',
            'required'      => true
        ],
        'denominator' => [
            'description'   => 'Denominator',
            'type'          => 'integer',
            'usage'         => 'numeric/integer',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*',
        'schema' => [
            'type'          => 'integer',
            'usage'          => 'numeric/integer',
            'qty'       => 'one'
        ]
    ],
    'access'        => [
        'visibility'        => 'public',
    ],
    'providers'     => ['context']
]);

list($context) = [$providers['context']];

$result = 0;

if ($params['denominator'] != 0) {
    $result = intdiv($params['numerator'], $params['denominator']);
}

$context->httpResponse()
    ->body($result)
    ->send();
