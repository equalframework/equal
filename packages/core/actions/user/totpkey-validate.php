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
    'description'   => 'Create a new totp key for a user.',
    'params'        => [
        'totpkey_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'core\security\factor\TotpKey',
            'description'       => 'The totpkey to validate.',
            'required'          => true
        ],
        'auth_code' => [
            'type'              => 'string',
            'description'       => 'Code that was given by the user\'s authenticator application.',
            'required'          => true
        ],
        'auth_token' => [
            'type'              => 'string',
            'description'       => 'Temporary token that certify that the user\'s credentials where recently given.',
            'help'              => 'Only needed if the authentication using totpkey is required for the user.'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'public' // #memo - allow access with temporary auth_token when MFA is required
    ],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

/**
 * Methods
 */

$checkToken = function($auth_token) use($auth) {
    $check = $auth->verifyToken($auth_token, constant('AUTH_SECRET_KEY'));
    if($check === false || $check <= 0) {
        throw new Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
    }

    $token = $auth->decodeToken($auth_token);

    $payload = $token['payload'] ?? null;
    $now = time();

    if($payload['type'] !== 'mfa_challenge' || $payload['amr'] !== 'pwd') {
        throw new Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
    }

    if((int) $payload['iat'] > $now || (int) $payload['exp'] < $now) {
        throw new Exception('expired_token', EQ_ERROR_INVALID_PARAM);
    }

    return $payload['sub'];
};


/**
 * Action
 */

$user_id = $auth->userId();

if($user_id <= 0) {
    if(empty($params['auth_token'])) {
        throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
    }

    $user_id = $checkToken($params['auth_token']);
}
elseif(!empty($params['auth_token'])) {
    throw new Exception('auth_token_not_allowed', EQ_ERROR_INVALID_PARAM);
}

$user = User::id($user_id)->first();

if(!$user) {
    throw new Exception('unexpected_error', EQ_ERROR_INVALID_USER);
}

$auth->su($user['id']);

$totpkey = TotpKey::id($params['totpkey_id'])
    ->read(['status'])
    ->first();

if(!$totpkey) {
    throw new Exception('unknown_totpkey', EQ_ERROR_UNKNOWN_OBJECT);
}

if($totpkey['status'] !== 'pending') {
    throw new Exception('cannot_validate_totpkey', EQ_ERROR_NOT_ALLOWED);
}

$res_auth_code = eQual::run('get', 'core_security_TotpKey_auth-code', ['id' => $totpkey['id']]);

if($params['auth_code'] !== $res_auth_code['auth_code']) {
    throw new Exception('auth_code_mismatch', EQ_ERROR_INVALID_PARAM);
}

TotpKey::id($totpkey['id'])->update(['status' => 'active']);

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
