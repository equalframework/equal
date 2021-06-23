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
    'constants'     => ['AUTH_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],    
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth', 'orm']
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

// retrieve current User identifier (HTTP headers lookup through Authentication Manager)
$user_id = $auth->userId();
// make sure user is authenticated
if($user_id <= 0) {
    throw new Exception('unknown_user', QN_ERROR_NOT_ALLOWED);    
}
// generate a new JWT access token
$expires = time() + AUTH_TOKEN_VALIDITY;
$token = $auth->token($user_id, $expires);

$context->httpResponse()
        ->cookie('access_token', $token, ['expires' => $expires, 'httponly' => true, 'secure' => AUTH_TOKEN_HTTPS])
        ->status(204)
        ->send();