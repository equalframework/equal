<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
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
            'required'      => true
        ],
        'password' =>  [
            'description'   => 'md5 value (32 chars) of the user password.',
            'type'          => 'string',
            'pattern'       => '/^[a-f0-9]{32}$/i',
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

$instance = User::create($params)->read(['id', 'login', 'firstname', 'lastname', 'language'])->adapt('txt')->first();

$context->httpResponse()
        ->status(201)
        ->body($instance)
        ->send();