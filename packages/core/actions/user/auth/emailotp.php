<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\factor\EmailOtpKey;
use core\setting\Setting;
use core\User;

[$params, $providers] = eQual::announce([
    'description'	=>	'Attempts to log a user in or elevate its privileges using an email totp.',
    'params' 		=>	[
        'auth_token' =>  [
            'type'          => 'string',
            'description'   => 'The temporary token that proves the correct password was given.'
        ],
        'auth_code' => [
            'type'          => 'string',
            'description'   => 'The authentication code that was emailed to the user.',
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
    'constants'     => ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

/**
 * Methods
 */

$checkToken = function($auth_token) use($auth) {
    try {
        $check = $auth->verifyToken($auth_token, constant('AUTH_SECRET_KEY'));
    }
    catch(Exception $e) {
        $check = false;
    }

    if($check === false || $check <= 0) {
        throw new Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
    }

    $token = $auth->decodeToken($auth_token);

    $payload = $token['payload'] ?? null;
    $now = time();

    $amr = $payload['amr'] ?? null;
    if($payload['type'] !== 'mfa_challenge' || ($amr !== ['pwd'] && $amr !== 'pwd')) {
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

$is_authenticated = true;
if($user_id <= 0) {
    $is_authenticated = false;
    if(empty($params['auth_token'])) {
        throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
    }

    $user_id = $checkToken($params['auth_token']);
}
elseif(!empty($params['auth_token'])) {
    throw new Exception('auth_token_not_allowed', EQ_ERROR_INVALID_PARAM);
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

$email_otp_key = EmailOtpKey::search([
    ['user_id', '=', $user['id']],
    ['status', '=', 'active']
])
    ->read(['code_hash', 'code_expires_at', 'failed_attempts'])
    ->first();

$now = time();
if($email_otp_key['code_expires_at'] < $now) {
    throw new Exception('code_expired', EQ_ERROR_NOT_ALLOWED);
}

if(!password_verify($params['auth_code'], $email_otp_key['code_hash'])) {
    $allowed_failed_attempts = Setting::get_value('core', 'security', 'auth.email_otp.allowed_failed_attempts', 5);

    $failed_attempts = $email_otp_key['failed_attempts'] + 1;
    if($failed_attempts > $allowed_failed_attempts) {
        EmailOtpKey::id($email_otp_key['id'])->transition('revoke');

        throw new Exception('failed_attempts_limit_reached', EQ_ERROR_NOT_ALLOWED);
    }
    else {
        EmailOtpKey::id($email_otp_key['id'])
            ->update(['failed_attempts' => $failed_attempts]);
    }

    throw new Exception('code_mismatch', EQ_ERROR_NOT_ALLOWED);
}

$auth_method = [
    'method'    => 'otp',
    'level'     => 2,
    'exp'       => $now + constant('AUTH_ACCESS_TOKEN_VALIDITY')
];

$jwt = $auth->retrieveAccessToken();

if($jwt) {
    // update the authentication state without extending the JWT lifetime
    $access_token = $auth->addAuthMethod($auth_method);
}
else {
    // generate a JWT access token
    $access_token = $auth->token($user['id'], constant('AUTH_ACCESS_TOKEN_VALIDITY'), $auth_method);
}

EmailOtpKey::id($email_otp_key['id'])
    ->transition('revoke');

$context
    ->httpResponse()
    ->cookie('access_token',  $access_token, [
        'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        'httponly'  => true,
        'secure'    => constant('AUTH_TOKEN_HTTPS')
    ])
    ->status(204)
    ->send();

