<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   => "Encrypts given value using private cipher key.",
    'params'        => [
        'value' => [
            'type'          => 'string',
            'description'   => 'Value to encrypt.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);


// initalise local vars with inputs
/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$result = config\encrypt($params['value']);

$context
    ->httpResponse()
    ->body(['result' => $result])
    ->status(204)
    ->send();