<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\User;

[$params, $providers] = eQual::announce([
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "The user name, username or login.",
            'type'          => 'string',
            'required'      => true
        ],
        'password' =>  [
            'description'   => "The user login.",
            'type'          => 'string',
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
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

// we might have received either a login (email) or a username

// if provided login is an email address, attempt to resolve by login
if(strpos($params['login'], '@') > 0) {
    // cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
    list($username, $domain) = explode('@', strtolower(trim($params['login'])));
    $username .= '+';
    $login = substr($username, 0, strpos($username, '+')).'@'.$domain;
}
// other format: attempt to resolve through username
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

$user = User::id($user_id)
    ->read(['validated', 'allow_auth'])
    ->first();

if(!$user || !$user['validated']) {
    throw new Exception('user_not_validated', EQ_ERROR_NOT_ALLOWED);
}

if(!$user['allow_auth']) {
    throw new Exception('not_allowed', EQ_ERROR_NOT_ALLOWED);
}

$mfa_method = $auth->getUserMfa();

if($mfa_method) {
    $now = time();
    $timeout_setting = $mfa_method === 'totp'
        ? 'auth.totp.timeout'
        : 'auth.email_otp.timeout';
    $validity = Setting::get_value('core', 'security', $timeout_setting, 300);

    if($mfa_method === 'email_otp') {
        eQual::run('do', 'core_user_emailotp-request');
    }

    $auth_token = $auth->issueMfaChallengeToken(
        $user['id'],
        $mfa_method,
        $validity,
        [[
            'method'    => 'pwd',
            'exp'       => $now + constant('AUTH_ACCESS_TOKEN_VALIDITY')
        ]]
    );

    $context
        ->httpResponse()
        ->body([
            'status'        => 'challenge',
            'auth_token'    => $auth_token,
            'challenge'     => [
                'method'        => $mfa_method
            ]
        ])
        ->send();
}
else {
    $auth_method = [
        'method'    => 'pwd',
        'exp'       => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY')
    ];

    $access_token = $auth->issueAccessToken($user['id'], $auth_method);

    $context
        ->httpResponse()
        ->cookie('access_token',  $access_token, [
            'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS')
        ])
        ->status(204)
        ->send();
}
