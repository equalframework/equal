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
    'providers'     => ['context', 'auth', 'orm']
]);

list($context, $om, $auth) = [ $providers['context'], $providers['orm'], $providers['auth']];

$validation = $om->validate('core\User', [], $params);
if($validation < 0 || count($validation)) {
    throw new Exception('invalid_credentials', QN_ERROR_INVALID_PARAM);
}

$auth->authenticate($params['login'], $params['password']);

$user_id = $auth->userId();
$user = User::id($user_id)
                ->read(['id', 'login', 'firstname', 'lastname', 'language'])
                ->adapt('txt')
                ->first();

// access token has to be renewed every 2 hours
$user['access_token'] = $auth->token($user_id, 2);
// refresh token is valid during 1 year
$user['refresh_token'] = $auth->token($user_id, 24*365);

$context->httpResponse()
        ->body($user)
        ->send();