<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Group;
use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Creates a new user account based in given credentials and details.',
    'params'        => [
        'login' => [
            'type'          => 'string',
            'usage'         => 'email',
            'description'   => 'email address to be used as login for the user.',
            'required'      => true
        ],
        'password' => [
            'type'          => 'string',
            'usage'         => 'password/nist',
            'description'   => 'the user chosen password.',
            'required'      => true
        ],
        'firstname' => [
            'type'          => 'string',
            'description'   => 'User given name.',
            'default'       => ''
        ],
        'lastname' => [
            'type'          => 'string',
            'description'   => 'User family name.',
            'default'       => ''
        ],
        'language' => [
            'type'          => 'string',
            'description'   => 'User language.',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
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

// create user: resulting Collection will check for current user privilege; validate the received values; and check the `Unique` constraints
// #memo - User class defines its own Unique constraint on `login` field, and User::onchangePassword method makes sure `password` is hashed
$user = User::create($params)
    ->read(['id', 'login', 'firstname', 'lastname', 'language'])
    ->adapt('json')
    ->first(true);

// try to assign the new user to default "users" group
Group::search(['name', '=', 'users'])->update(['users_ids' => [+$user['id']]]);

$context
    ->httpResponse()
    ->status(201)
    ->body($user)
    ->send();
