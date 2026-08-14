<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\factor\TotpKey;
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
    'providers'     => ['context', 'auth'],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']
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

$user = User::id($user_id)->read(['validated'])->first(true);

if(!$user || !$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

$totp_required = false;

$global_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
$totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled', $global_totp_enabled, ['user_id' => $user['id']]);
if($totp_enabled) {
    $global_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
    $totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required', $global_totp_required, ['user_id' => $user['id']]);
    if(!$totp_required) {
        // check if user configured a totpkey even if it isn't required
        $totpkey = TotpKey::search([
            ['user_id', '=', $user['id']],
            ['type', '=', 'totp'],
            ['status', '=', 'active']
        ])
            ->first();

        if($totpkey) {
            $totp_required = true;
        }
    }
}

if($totp_required) {
    $now = time();
    $auth_token = $auth->encodeToken([
        'type'  => 'mfa_challenge',
        'amr'   => 'pwd',
        'sub'   => $user['id'],
        'iat'   => $now,
        'exp'   => $now + 300
    ]);

    $totpkey = TotpKey::search([
        ['user_id', '=', $user['id']],
        ['type', '=', 'totp'],
        ['status', '=', 'active']
    ])
        ->read(['failed_attempts'])
        ->first();

    if($totpkey && $totpkey['failed_attempts'] >= 5) {
        throw new Exception('failled_attempts_reached');
    }

    $context
        ->httpResponse()
        ->body([
            'mfa_required'  => true,
            'auth_token'    => $auth_token
        ])
        ->send();
}
else {
    // generate a JWT access token
    $access_token = $auth->token(
        // user identifier
        $user_id,
        // validity of the token
        constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        // authentication method to register to AMR
        [
            'auth_type'  => 'pwd',
            'auth_level' => 1
        ]
    );

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
