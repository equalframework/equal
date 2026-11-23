<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Updates a given package name.",
    'help'          => "Still not sure how to handle this since the package name might be referenced in many places.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package for which the name has to be updated.',
            'required'      => true
        ],
        'name' => [
            'type'          => 'string',
            'description'   => 'New name for the package.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;


$context
    ->httpResponse()
    ->body($result)
    ->send();
