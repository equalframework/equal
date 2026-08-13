<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\factor\TotpKey;
use core\User;

[$params, $providers] = eQual::announce([
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "The user name, username or login.",
            'type'          => 'string',
            'required'      => true
        ],
        'auth_token' =>  [
            'description'   => "The temporary token that proves the correct password was given.",
            'type'          => 'string',
            'required'      => true
        ],
        'auth_code' => [
            'description'   => "The code given by the user's authenticator application.",
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
    'constants'     => ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']
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

// find the user related to the normalized login
$user = User::search(['login', '=', $login])
    ->read(['id', 'validated', 'allow_auth'])
    ->first(true);

if(!$user) {
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

if(!$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

$check = $auth->verifyToken($params['auth_token'], constant('AUTH_SECRET_KEY'));
if($check === false || $check <= 0) {
    throw new Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
}

$token = $auth->decodeToken($params['auth_token']);

$payload = $token['payload'] ?? null;
$now = time();

if($payload['type'] !== 'mfa_challenge' || $payload['amr'] !== 'pwd' || $payload['sub'] !== $user['id']) {
    throw new Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
}

if((int) $payload['iat'] > $now || (int) $payload['exp'] < $now) {
    throw new Exception('expired_token', EQ_ERROR_INVALID_PARAM);
}

$totpkey = TotpKey::search([
    ['user_id', '=', $user['id']],
    ['type', '=', 'totp'],
    ['status', '=', 'active']
])
    ->first();

if(!$totpkey) {
    throw new Exception('totpkey_not_found', EQ_ERROR_NOT_ALLOWED);
}

// authorize the internal protected provider with the user proven by the MFA challenge
$auth->su($user['id']);
$auth_code_res = eQual::run('get', 'core_security_TotpKey_auth-code', ['id' => $totpkey['id']]);

if(!isset($auth_code_res['auth_code']) || !hash_equals($auth_code_res['auth_code'], $params['auth_code'])) {
    throw new Exception('auth_code_mismatch', EQ_ERROR_INVALID_PARAM);
}

// generate a JWT access token
$access_token = $auth->token(
    // user identifier
    $user['id'],
    // validity of the token
    constant('AUTH_ACCESS_TOKEN_VALIDITY'),
    // authentication method to register to AMR
    [
        'auth_type'  => 'totp',
        'auth_level' => 2
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
