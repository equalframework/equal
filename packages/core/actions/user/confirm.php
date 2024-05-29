<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
list($params, $providers) = eQual::announce([
    'description'	=>	"Validate a user subscription. This controller is meant to be requested through a link sent by email.",
    'params' 		=>	[
        'code' => [
            'description'   => 'Code for authenticating user.',
            'type'          => 'string',
            'required'      => true
        ],
        'redirect' => [
            'description'   => 'Relative URL to redirect user to, after successful authentication.',
            'type'          => 'string',
            'usage'         => 'url',
            'default'       => 'auth/#/signin'
        ]
    ],
    'constants'     => ['BACKEND_URL'],
    'access'        => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


// initialize local vars with inputs
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

list($login, $password) = explode(':', base64_decode($params['code']));

$auth->su();

// received password is expected to be encrypted the same way it is stored
$ids = $om->search('core\User', [['login', '=', $login]]);

if(!count($ids)) {
    throw new Exception('invalid_request', EQ_ERROR_INVALID_USER);
}

$list = $om->read(User::getType(), $ids, ['id', 'login', 'password']);
$user = reset($list);

if(!password_verify($password, $user['password'])) {
    throw new \Exception('invalid_request', EQ_ERROR_INVALID_USER);
}

// mark user as validated (will update status according to USER_ACCOUNT_VALIDATION)
$om->update(User::getType(), $user['id'], ['validated' => true]);

$response = $context->httpResponse();

if(strlen($params['redirect'])) {
    $url = rtrim(constant('BACKEND_URL'), '/').'/'.ltrim($params['redirect'], '/');
    header('Location: '.$url);
    exit();
}

$context->httpResponse()
        ->status(204)
        ->send();
