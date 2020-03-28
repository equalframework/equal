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
            'required'      => true
        ],
        'password' =>  [
            'description'   => 'the user chosen password.',
            'type'          => 'string',
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
        ]
    ],
    'providers'     => ['context', 'orm'] 
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// Encrypt password
$params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);

// Resulting Collection will check for current user privilege; validate the received values
$instance = User::create($params)
                ->read(['id', 'login', 'firstname', 'lastname', 'language'])
                ->adapt('txt')
                ->first();

$context->httpResponse()
        ->status(201)
        ->body($instance)
        ->send();