<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Create a new object by cloning an existing object.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class of original object(s).',
            'required'      => true
        ],
        'ids' => [
            'type'          => 'array',
            'description'   => 'Identifiers of original objects.',
            'required'      => true
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Language of target object (applied to multilang fields).',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$collection = $params['entity']::ids($params['ids'])
            ->clone($params['lang'])
            ->get(true);

$context
    ->httpResponse()
    ->status(201)
    ->body([])
    ->send();