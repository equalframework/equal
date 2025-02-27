<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => "Updates a given package name.",
    'help'          => "Still not sure how to handle this since the package name might be referenced in many places.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package'    => [
            'description'   => 'Name of the package for which the name has to be updated.',
            'type'          => 'string',
            'required'      => true
        ],
        'name' =>  [
            'description'   => 'New name for the package.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];


$context->httpResponse()
        ->body($result)
        ->send();
