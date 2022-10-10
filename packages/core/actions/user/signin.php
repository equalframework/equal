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
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],    
    'providers'     => ['context', 'auth', 'orm'],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_REFRESH_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']    
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

$validation = $om->validate('core\User', [], $params);
if($validation < 0 || count($validation)) {
    throw new Exception('invalid_credentials', QN_ERROR_INVALID_PARAM);
}

$auth->authenticate($params['login'], $params['password']);

$user_id = $auth->userId();

if(!$user_id) {
    // this is a fallback exception, but we should never reach this code, since user has been found by authenticate method
    throw new Exception("user_not_found", QN_ERROR_INVALID_USER);
}

$user = User::id($user_id)->read(['validated'])->first(true);
if(!$user || !$user['validated']) {
    throw new Exception("user_not_validated", QN_ERROR_NOT_ALLOWED);
}

// generate a JWT access token
$access_token  = $auth->token($user_id, AUTH_ACCESS_TOKEN_VALIDITY);
$refresh_token = $auth->token($user_id, AUTH_REFRESH_TOKEN_VALIDITY);

$context->httpResponse()
        ->cookie('access_token',  $access_token, [
            'expires'   => time() + AUTH_ACCESS_TOKEN_VALIDITY, 
            'httponly'  => true, 
            'secure'    => AUTH_TOKEN_HTTPS
        ])
        ->cookie('refresh_token', $refresh_token, [
            'expires'   => time() + AUTH_REFRESH_TOKEN_VALIDITY, 
            'httponly'  => true, 
            'secure'    => AUTH_TOKEN_HTTPS
        ])
        ->status(204)
        ->send();