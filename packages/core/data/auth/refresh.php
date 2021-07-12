<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Attempts to renew an access token that is about to expire (but still valid).",
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth', 'orm'],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_REFRESH_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

try {
    // retrieve current User identifier (HTTP headers lookup through Authentication Manager)
    $user_id = $auth->userId();
}
catch(Exception $e) {
    // intercept auth_expired_token exception, if any 
    $user_id = 0;
}

// check if access token is still valid
if($user_id > 0) {
    throw new Exception('valid_access_token', QN_ERROR_NOT_ALLOWED);    
}

$refresh_token = $context->httpRequest()->cookie('refresh_token');
if(!$refresh_token) {
    throw new Exception('missing_token', QN_ERROR_INVALID_PARAM);
}

// inject refresh token as access token and try to authenticate
$context->httpRequest()->cookie('access_token', $refresh_token);
$jwt = $context->httpRequest()->cookie('access_token');

$user_id = $auth->userId();

// make sure user is now authenticated
if($user_id <= 0) {
    throw new Exception('invalid_token', QN_ERROR_INVALID_PARAM);
}

// generate a new JWT access token
$token = $auth->token($user_id, AUTH_ACCESS_TOKEN_VALIDITY);

// #memo - refresh token validity cannot be extended

$context->httpResponse()
        ->cookie('access_token', $token, [
            'expires'   => time() + AUTH_ACCESS_TOKEN_VALIDITY, 
            'httponly'  => true, 
            'secure'    => AUTH_TOKEN_HTTPS
        ])
        ->status(204)
        ->send();