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
            'description'       => 'Temporary token that certifies that the user\'s credentials were recently given.',
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
 * Action
 */

$user_id = $auth->userId();

$is_authenticated = true;
if($user_id <= 0) {
    $is_authenticated = false;
    if(empty($params['auth_token'])) {
        throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
    }

    $mfa_challenge = $auth->verifyMfaChallengeToken($params['auth_token'], 'totp');
    $user_id = $mfa_challenge['sub'];
}
elseif(!empty($params['auth_token'])) {
    throw new Exception('auth_token_not_allowed', EQ_ERROR_INVALID_PARAM);
}

$user = User::id($user_id)->first();

if(!$user) {
    throw new Exception('unexpected_error', EQ_ERROR_INVALID_USER);
}

if(!$is_authenticated) {
    $auth->su($user['id']);
}

$global_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
$totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled', $global_totp_enabled, ['user_id' => $user['id']]);

if(!$totp_enabled) {
    throw new Exception("totp_auth_disabled", EQ_ERROR_NOT_ALLOWED);
}

$global_auth_password_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
$auth_password_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required', $global_auth_password_totp_required, ['user_id' => $user['id']]);

if(!$auth_password_totp_required) {
    $global_totpkey_creation = Setting::get_value('core', 'security', 'auth.totp.creation');
    $totpkey_creation = Setting::get_value('core', 'security', 'auth.totp.creation', $global_totpkey_creation, ['user_id' => $user['id']]);

    if(!$totpkey_creation) {
        throw new Exception("totpkey_creation_not_allowed", EQ_ERROR_NOT_ALLOWED);
    }
}

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
