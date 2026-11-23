<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns the sum of a list of integer',
    'params'        => [
        'list' => [
            'type'          => 'array',
            'description'   => 'list of integer',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*',
        'schema'        => [
            'type'          => 'integer',
            'usage'     => 'numeric/integer',
            'qty'       => 'one'
        ]
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$result = 0;

foreach ($params['list'] as $element) {
    $result += $element;
}

$context
    ->httpResponse()
    ->body($result)
    ->send();
