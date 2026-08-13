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
    'description'   => 'Create a new totp key for a user.',
    'params'        => [
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

$res_fields = ['algorithm', 'digits', 'period', 'totp_uri', 'totp_qr_code_uri'];

$totpkey = TotpKey::search([
    ['user_id', '=', $user['id']],
    ['type', '=', 'totp'],
    ['status', '=', 'pending']
])
    ->read($res_fields)
    ->first(true);

if(!$totpkey) {
    $data = [
        'user_id'   => $user['id'],
        'status'    => 'pending'
    ];

    foreach(['algorithm', 'digits', 'period'] as $key) {
        $global_value = Setting::get_value('core', 'security', "auth.totp.$key");
        $value = Setting::get_value('core', 'security', "auth.totp.$key", $global_value, ['user_id' => $user['id']]);
        if($value) {
            $data[$key] = $value;
        }
    }

    $totpkey = TotpKey::create($data)
        ->read($res_fields)
        ->first(true);
}

$context
    ->httpResponse()
    ->body($totpkey)
    ->send();
