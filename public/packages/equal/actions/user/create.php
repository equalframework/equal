<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = announce([
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
            'usage'         => 'password/NIST',
            'required'      => true
        ],
        'firstname' =>  [
            'description'   => 'User firstname.',
            'type'          => 'string', 
            'default'       => ''
        ],
        'lastname' => [
            'description'   => 'User lastname.',
            'type'          => 'string',
            'default'       => ''
        ],
        'language' => [
            'description'   => 'User language.',
            'type'          => 'string',
            'default'       => DEFAULT_LANG
        ]        
    ],
    'providers'     => ['context', 'orm'] 
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// todo: deprecate
// @reason: User class defines its own Unique constraint
/*
// make sure no account by this email address already exists
$ids = User::search( ['login', '=', $params['login']] )
           ->ids();
if(count($ids)) {
    throw new Exception("user_already_registered", QN_ERROR_NOT_ALLOWED);
}
*/

// Encrypt password
$params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);

// Resulting Collection will check for current user privilege; validate the received values; and check the `Unique` constraints
$instance = User::create($params)
                ->read(['id', 'login', 'firstname', 'lastname', 'language'])
                ->adapt('txt')
                ->first();

$context->httpResponse()
        ->status(201)
        ->body($instance)
        ->send();