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
        'user_id' =>  [
            'description'   => 'Identifier of the user to update (when not current user).',
            'type'          => 'integer',
            'default'       => 0
        ],
        'token' => [
            'type'          => 'string',
            'description'   => 'access token in case of password recovery with email',
            'default'      => ''
        ],
        'password' =>  [
            'description'   => 'New password.',
            'type'          => 'string',
            'usage'         => 'password',
            'required'      => true
        ],
        'confirm' =>  [
            'description'   => 'Confirmation of the password.',
            'type'          => 'string',
            'usage'         => 'password',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

list($context, $orm, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

if(strcmp($params['password'], $params['confirm']) != 0) {
    throw new Exception('password_confirm_mismatch', QN_ERROR_INVALID_PARAM);
}

if(strlen($params['token'])) {
    // use received token to authenticate
    $user_id = $auth->userId($params['token']);
}
else {
    $user_id = $auth->userId();
}

if(!$user_id) {
    throw new \Exception("user_not_found", QN_ERROR_INVALID_USER);    
}

$target_user_id = $params['user_id'];
if($target_user_id <= 0) {
    $target_user_id = $user_id;
}

// update user instance (user always has write access on its own object)
// #memo - User::onchangePassword method makes sure `password` is hashed 
$instance = User::id($target_user_id)
    ->update([
        'password' => $params['password']
    ])
    ->adapt('txt')
    ->first();

$response = $context->httpResponse();

// if a token was provided, reply with new access tokens
if(strlen($params['token'])) {
    // generate a JWT access token
    $access_token  = $auth->token($user_id, AUTH_ACCESS_TOKEN_VALIDITY);
    $refresh_token = $auth->token($user_id, AUTH_REFRESH_TOKEN_VALIDITY);
    $response->cookie('access_token',  $access_token, [
        'expires'   => time() + AUTH_ACCESS_TOKEN_VALIDITY, 
        'httponly'  => true, 
        'secure'    => AUTH_TOKEN_HTTPS
    ])
    ->cookie('refresh_token', $refresh_token, [
        'expires'   => time() + AUTH_REFRESH_TOKEN_VALIDITY, 
        'httponly'  => true, 
        'secure'    => AUTH_TOKEN_HTTPS
    ]);
}

$response->status(204)
         ->send();