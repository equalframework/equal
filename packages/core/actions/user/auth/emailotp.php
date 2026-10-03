<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\challenge\EmailOtpChallenge;
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
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

/**
 * Action
 */

$user_id = $auth->userId();
$mfa_challenge = null;

$is_authenticated = true;
if($user_id <= 0) {
    $is_authenticated = false;
    if(empty($params['auth_token'])) {
        throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
    }

    $mfa_challenge = $auth->verifyMfaChallengeToken($params['auth_token'], 'email_otp');
    $user_id = $mfa_challenge['sub'];
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

$email_otp_challenge = EmailOtpChallenge::search([
        ['user_id', '=', $user['id']],
        ['status', '=', 'pending']
    ])
    ->read(['code_hash', 'expires_at', 'failed_attempts'])
    ->first();

$now = time();
if(!$email_otp_challenge) {
    throw new Exception('email_otp_key_expired', EQ_ERROR_NOT_ALLOWED);
}

if($email_otp_challenge['expires_at'] < $now) {
    EmailOtpChallenge::id($email_otp_challenge['id'])
        ->update(['invalidated_reason' => 'expired'])
        ->transition('invalidate');

    throw new Exception('email_otp_key_expired', EQ_ERROR_NOT_ALLOWED);
}

if(!password_verify($params['auth_code'], $email_otp_challenge['code_hash'])) {
    $allowed_failed_attempts = Setting::get_value('core', 'security', 'auth.email_otp.allowed_failed_attempts', 5);

    $failed_attempts = $email_otp_challenge['failed_attempts'] + 1;
    if($failed_attempts >= $allowed_failed_attempts) {
        EmailOtpChallenge::id($email_otp_challenge['id'])
            ->update(['invalidated_reason' => 'failed_attempts'])
            ->transition('invalidate');

        throw new Exception('allowed_failed_attempts_reached', EQ_ERROR_NOT_ALLOWED);
    }
    else {
        EmailOtpChallenge::id($email_otp_challenge['id'])
            ->update(['failed_attempts' => $failed_attempts]);
    }

    throw new Exception('auth_code_mismatch', EQ_ERROR_NOT_ALLOWED);
}

$previous_auth_methods = [];
if(!$is_authenticated) {
    $previous_auth_methods = $mfa_challenge['auth'] ?? [[
        'method'    => 'pwd',
        'exp'       => $now + constant('AUTH_ACCESS_TOKEN_VALIDITY')
    ]];
}

$auth_method = [
    'method'    => 'email_otp',
    'exp'       => $now + constant('AUTH_ACCESS_TOKEN_VALIDITY')
];

$access_token = $auth->issueAccessToken($user['id'], $auth_method, $previous_auth_methods);

EmailOtpChallenge::id($email_otp_challenge['id'])
    ->transition('consume');

$context
    ->httpResponse()
    ->cookie('access_token',  $access_token, [
        'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        'httponly'  => true,
        'secure'    => constant('AUTH_TOKEN_HTTPS')
    ])
    ->status(204)
    ->send();
