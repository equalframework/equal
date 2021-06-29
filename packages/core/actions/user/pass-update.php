<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = announce([
    'description'   => 'Updates the password related to a user account.',
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
        'password' =>  [
            'description'   => 'New password.',
            'type'          => 'string',
            'usage'         => 'password/NIST',
            'required'      => true
        ],
        'confirmation' =>  [
            'description'   => 'Confirmation of the password.',
            'type'          => 'string',
            'usage'         => 'password/NIST',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm'] 
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// todo : handle `usage` limitations

if(strcmp($params['password'], $params['confirmation']) != 0) {
    throw new Exception('password_confirmation_mismatch', QN_ERROR_INVALID_PARAM);
}

// Encrypt password
$params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);


// update user instance (user has write access on its own object)
$instance = User::id($params['id'])
    ->update([
        'password' => $params['password']
    ])
    ->adapt('txt')
    ->first();

$context->httpResponse()
        ->status(204)
        ->body([])
        ->send();