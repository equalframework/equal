<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "user name",
            'type'          => 'string',
            'required'      => true
        ],
        'password' =>  [
            'description'   => "user password",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'constants'     => ['AUTH_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],    
    'providers'     => ['context', 'auth', 'orm']
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

$validation = $om->validate('core\User', [], $params);
if($validation < 0 || count($validation)) {
    throw new Exception('invalid_credentials', QN_ERROR_INVALID_PARAM);
}

$auth->authenticate($params['login'], $params['password']);

$user_id = $auth->userId();

// generate a JWT access token
$expires = time() + AUTH_TOKEN_VALIDITY;
$token = $auth->token($user_id, $expires);

$context->httpResponse()
        ->cookie('access_token', $token, ['expires' => $expires, 'httponly' => true, 'secure' => AUTH_TOKEN_HTTPS])
        ->status(204)
        ->send();