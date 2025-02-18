<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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
