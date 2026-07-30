<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'	=>	"Validate a user subscription. This controller is meant to be requested through a link sent by email.",
    'params' 		=>	[
        'code' => [
            'description'   => 'Code for authenticating user.',
            'type'          => 'string',
            'required'      => true
        ],
        'redirect' => [
            'description'   => 'Relative URL to redirect user to, after successful authentication. A reset token is appended to it.',
            'type'          => 'string',
            'usage'         => 'url',
            'default'       => 'auth/#/reset'
        ]
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_SECRET_KEY'],
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
['orm' => $orm, 'context' => $context, 'auth' => $auth] = $providers;

[$login, $password] = explode(':', base64_decode($params['code']));

$auth->su();

// received password is expected to be encrypted the same way it is stored
$ids = $orm->search('core\User', [['login', '=', $login]]);

if(!count($ids)) {
    throw new Exception('invalid_request', EQ_ERROR_INVALID_USER);
}

$list = $orm->read(User::getType(), $ids, ['id', 'login', 'password']);
$user = reset($list);

if(!password_verify($password, $user['password'])) {
    throw new \Exception('invalid_request', EQ_ERROR_INVALID_USER);
}

// mark user as validated (will update status according to USER_ACCOUNT_VALIDATION)
$orm->update(User::getType(), $user['id'], ['validated' => true]);

$response = $context->httpResponse();

if(strlen($params['redirect'])) {
    // Generate a reset token valid for 15 minutes, same as password recovery links.
    $token = $auth->token($user['id'], 60 * 15);
    $url = rtrim(constant('BACKEND_URL'), '/') . '/' . trim($params['redirect'], '/') . '/' . $token;
    header('Location: ' . $url);
    exit();
}

$context->httpResponse()
        ->status(204)
        ->send();
