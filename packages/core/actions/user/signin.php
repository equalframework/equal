<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\User;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   => "Attempts to log a user in.",
    'deprecated'    => "Use user_auth_pwd instead.",
    'params'        => [
        'login' => [
            'type'          => 'string',
            'description'   => "user name",
            'required'      => true
        ],
        'password' => [
            'type'          => 'string',
            'description'   => "user password",
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'public'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

// we might have received either a login (email) or a username
if(strpos($params['login'], '@') > 0) {
    // cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
    list($username, $domain) = explode('@', strtolower(trim($params['login'])));
    $username .= '+';
    $login = substr($username, 0, strpos($username, '+')).'@'.$domain;
}
else {
    // find a user that matches the given username (there should be only one)
    $user = User::search(['username', '=', $params['login']])->read(['login'])->first();
    if(!$user) {
        throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
    }
    $login = $user['login'];
}

// #memo - email might still be invalid (a validation check is made in User class)
$auth->authenticate($login, $params['password']);

$user_id = $auth->userId();

if(!$user_id) {
    // this is a fallback exception, but we should never reach this code, since user has been found by authenticate method
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

$user = User::id($user_id)->read(['validated'])->first(true);

if(!$user || !$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

$global_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
if(Setting::get_value('core', 'security', 'auth.password.totp_required', $global_totp_required, ['user_id' => $user['id']])) {
    throw new Exception('totp_required', EQ_ERROR_NOT_ALLOWED);
}

$auth_method = [
    'method'    => 'pwd',
    'exp'       => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY')
];

$jwt = $auth->retrieveAccessToken();
if($jwt && (int) $jwt['id'] !== (int) $user_id) {
    throw new Exception('authenticated_user_mismatch', EQ_ERROR_NOT_ALLOWED);
}

if($jwt) {
    $access_token = $auth->addAuthMethod($auth_method);
}
else {
    $access_token = $auth->token($user_id, constant('AUTH_ACCESS_TOKEN_VALIDITY'), $auth_method);
}

$context
    ->httpResponse()
    ->cookie('access_token',  $access_token, [
            'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS'),
            'samesite'  => 'Strict'
        ])
    ->status(204)
    ->send();
