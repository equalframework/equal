<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = announce([
    'description'   => 'Updates a user account based on given details.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],    
    'params'        => [
        'id' =>  [
            'description'   => 'Identifier of the user to update.',
            'type'          => 'integer',
            'required'      => true
        ],
// todo : hanle password update
/*        
        'password' =>  [
            'description'   => 'the user chosen password.',
            'type'          => 'string',
            'usage'         => 'password/NIST',
            'required'      => true
        ],
*/        
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

// todo: 
/*
in case of password change: require old password as well
// Encrypt password
$params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
*/

// update user instance
$instance = User::id($params['id'])
    ->update([
        'firstname' => $params['firstname'], 
        'lastname'  => $params['lastname'], 
        'language'  => $params['language'], 
    ])
    ->adapt('txt')
    ->first();

$context->httpResponse()
        ->status(200)
        ->body($instance)
        ->send();