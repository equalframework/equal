<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempts to renew the access token based on a refresh token.",
    'params' 		=>	[
        'refresh_token'		=>	[
            'description'   => "refresh token for requesting a new access token",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],    
    'providers'     => ['context', 'auth', 'orm']
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

if( !$auth->verifyToken($params['refresh_token'], AUTH_SECRET_KEY) ) {
    throw new \Exception('JWT_invalid_signature');
}

$decoded = $auth->decodeToken($params['refresh_token']);

if(!isset($decoded['payload']['exp']) || !isset($decoded['payload']['id']) || $decoded['payload']['id'] <= 0) {
    throw new \Exception('JWT_invalid_payload');
}

$payload = $decoded['payload'];

if($payload['exp'] < time()) {
    throw new \Exception('auth_expired_token', QN_ERROR_INVALID_USER);
}

$user_id = $payload['id'];

// generate a new pair of tokens
$response = [
    // access token has to be renewed every 2 hours    
    'access_token'  => $auth->token($user_id, 2),
    // refresh token is valid during 1 year    
    'refresh_token' => $auth->token($user_id, 24*365)
];

$context->httpResponse()
        ->body($response)
        ->send();