<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
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

$validation = $om->validate('core\User', $params);
if($validation < 0 || count($validation)) {
    throw new Exception('invalid credentials: '.implode(',', $validation), QN_ERROR_INVALID_PARAM);
}

$auth->authenticate($params['login'], $params['password']);

$instance = User::id($auth->userId())
                ->read(['id', 'login', 'firstname', 'lastname', 'language'])
                ->adapt('txt')
                ->first();
                
$instance['access_token'] = $auth->token();

$context->httpResponse()
        ->body($instance)
        ->send();