<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Group;
use core\User;

list($params, $providers) = eQual::announce([
    'description'   => 'Creates a new user account based in given credentials and details.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'login' =>  [
            'description'   => 'email address to be used as login for the user.',
            'type'          => 'string',
            'usage'         => 'email',
            'required'      => true
        ],
        'password' =>  [
            'description'   => 'the user chosen password.',
            'type'          => 'string',
            'usage'         => 'password/nist',
            'required'      => true
        ],
        'firstname' =>  [
            'description'   => 'User given name.',
            'type'          => 'string',
            'default'       => ''
        ],
        'lastname' => [
            'description'   => 'User family name.',
            'type'          => 'string',
            'default'       => ''
        ],
        'language' => [
            'description'   => 'User language.',
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'access'        => [
        'visibility'        => 'private'
    ],
    'providers'     => ['context', 'orm']
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// create user: resulting Collection will check for current user privilege; validate the received values; and check the `Unique` constraints
// #memo - User class defines its own Unique constraint on `login` field, and User::onchangePassword method makes sure `password` is hashed
$user = User::create($params)
    ->read(['id', 'login', 'firstname', 'lastname', 'language'])
    ->adapt('json')
    ->first(true);

// try to assign the new user to default "users" group
Group::search(['name', '=', 'users'])->update(['users_ids' => [+$user['id']]]);

$context->httpResponse()
        ->status(201)
        ->body($user)
        ->send();
