<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Updates the password related to a user account.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'user_id' =>  [
            'description'       => 'Identifier of the user to update (when not current user).',
            'type'              => 'many2one',
            'foreign_object'    => 'identity\User',
            'default'           => 0
        ],
        'token' => [
            'type'              => 'string',
            'description'       => 'access token in case of password recovery with email',
            'default'           => ''
        ],
        'password' =>  [
            'description'       => 'New password.',
            'type'              => 'string',
            'usage'             => 'password',
            'required'          => true
        ],
        'confirm' =>  [
            'description'       => 'Confirmation of the password.',
            'type'              => 'string',
            'usage'             => 'password',
            'required'          => true
        ]
    ],
    'access' => [
        'visibility' => 'public'
    ],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'orm', 'auth', 'access']
]);

['context' => $context, 'orm' => $orm, 'auth' => $auth, 'access' => $access] = $providers;

if(strcmp($params['password'], $params['confirm']) != 0) {
    throw new Exception('password_confirm_mismatch', EQ_ERROR_INVALID_PARAM);
}

if(strlen($params['token'])) {
    $user_id = $auth->userId($params['token']);

    if(!$user_id) {
        throw new Exception('invalid_token', EQ_ERROR_INVALID_USER);
    }

    $target_user_id = $user_id;
}
else {
    $user_id = $auth->userId();
    $target_user_id = ($params['user_id']) ? $params['user_id'] : $user_id;
}

$targetUser = User::id($target_user_id)->first();

if(!$targetUser) {
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

if(!$user_id || ($target_user_id !== $user_id && !$access->hasRight(EQ_R_MANAGE, User::getType(), [$target_user_id]))) {
    throw new Exception("restricted_operation", EQ_ERROR_NOT_ALLOWED);
}

// update target user using root account
try {
    $auth->su();
    // #memo - User::onchangePassword method makes sure `password` is hashed
    User::id($target_user_id)
        ->update([
            'password' => $params['password']
        ]);
}
finally {
    $auth->su($user_id);
}

$response = $context->httpResponse();

// if a token was provided, include a new access token in response
if(strlen($params['token'])) {
    // generate a JWT access token
    $access_token  = $auth->token($user_id, constant('AUTH_ACCESS_TOKEN_VALIDITY'));
    $response->cookie('access_token',  $access_token, [
        'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        'httponly'  => true,
        'secure'    => constant('AUTH_TOKEN_HTTPS'),
        'samesite'  => 'Strict'
    ]);
}

$response->status(204)
         ->send();