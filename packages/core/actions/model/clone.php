<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Create a new object by cloning an existing object.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class of original object(s).',
            'type'          => 'string',
            'required'      => true
        ],
        'ids' =>  [
            'description'   => 'Identifiers of original objects.',
            'type'          => 'array',
            'required'      => true
        ],
        'lang' => [
            'description '  => 'Language of target object (applied to multilang fields).',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [ $providers['context'], $providers['orm'] ];

$collection = $params['entity']::ids($params['ids'])
            ->clone($params['lang'])
            ->get(true);

$context->httpResponse()
        ->status(201)
        ->body([])
        ->send();