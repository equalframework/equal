<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Passkey;
use core\setting\Setting;
use core\setting\SettingValue;
use core\User;
use equal\auth\JWT;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns the options needed for passkey authentication.',
    'params'        => [
        'user_handle' => [
            'type'          => 'string',
            'description'   => 'Anonymous user handle.'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'public'
    ],
    'constants'     => ['AUTH_SECRET_KEY', 'APP_NAME', 'BACKEND_URL'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

$setting = Setting::search(['name', '=', 'core.security.passkey_user-handle'])
    ->read(['id'])
    ->first();

$user_handle_setting = SettingValue::search([['setting_id', '=', $setting['id']], ['value', '=', $params['user_handle']]])
    ->read(['user_id'])
    ->first(true);

if(!$user_handle_setting) {
    throw new Exception('invalid_user_handle', EQ_ERROR_INVALID_PARAM);
}

$user = User::search(['id', '=', $user_handle_setting['user_id']])
    ->read(['id', 'login', 'username'])
    ->first(true);

if(!$user) {
    throw new Exception('user_not_found', EQ_ERROR_UNKNOWN_OBJECT);
}

$rp_id = Setting::get_value('core', 'security', 'passkey_rp_id', parse_url(constant('BACKEND_URL'), PHP_URL_HOST));
$rp_name = Setting::get_value('core', 'security', 'passkey_rp_name', constant('APP_NAME'));
$user_verification = Setting::get_value('core', 'security', 'passkey_user_verification', 'preferred');

$cross_platform_attachment = Setting::get_value('core', 'security', 'passkey_cross_platform', true);
if($cross_platform_attachment === 'all') {
    $cross_platform_attachment = null;
}
elseif($cross_platform_attachment === 'cross-platform') {
    $cross_platform_attachment = true;
}
elseif($cross_platform_attachment === 'platform') {
    $cross_platform_attachment = false;
}

$passkey_formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
$allowed_formats = [];
foreach($passkey_formats as $format) {
    $is_format_allowed = Setting::get_value('core', 'security', "passkey_format_$format", true);
    if($is_format_allowed) {
        $allowed_formats[] = $format;
    }
}

// #memo - creating WbAuthn with an empty formats array makes the process fail silently
$webAuthn = new WebAuthn($rp_name, $rp_id, $allowed_formats);

$credential_ids = [];

if(!empty($params['login'])) {
    $passkeys = Passkey::search(['user_id', '=', $user['id']])
        ->read(['credential_id'])
        ->get(true);

    if(empty($passkeys)) {
        throw new Exception('no_passkey_for_user', EQ_ERROR_INVALID_PARAM);
    }

    $credential_ids = array_map(
        function($credential_id) {
            return ByteBuffer::fromHex($credential_id);
        },
        array_column($passkeys, 'credential_id')
    );
}

$auth_options = $webAuthn->getGetArgs(
    $credential_ids,
    20,
    Setting::get_value('core', 'security', 'passkey_authenticator_usb', true),
    Setting::get_value('core', 'security', 'passkey_authenticator_nfc', true),
    Setting::get_value('core', 'security', 'passkey_authenticator_ble', true),
    Setting::get_value('core', 'security', 'passkey_authenticator_hybrid', true),
    Setting::get_value('core', 'security', 'passkey_authenticator_internal', true),
    $user_verification
);

$auth_options->authToken = JWT::encode(
    ['challenge' => $webAuthn->getChallenge()->getHex()],
    constant('AUTH_SECRET_KEY')
);

$context->httpResponse()
        ->status(200)
        ->body((array) $auth_options)
        ->send();
