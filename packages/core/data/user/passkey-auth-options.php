<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Passkey;
use core\setting\Setting;
use core\User;
use equal\auth\JWT;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;

[$params, $providers] = announce([
    'description'   => 'Returns the options needed for passkey authentication.',
    'params'        => [
        'login' => [
            'type'          => 'string',
            'description'   => 'Username.'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'    => 'public'
    ],
    'constants'     => ['AUTH_SECRET_KEY', 'APP_NAME', 'BACKEND_URL'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

/**
 * Methods
 */

$getUserFromLoginParam = function(array $params): array {
    $domain = [];
    if(strpos($params['login'], '@') > 0) {
        // cleanup provided email (as login): we strip heading and trailing spaces and remove recipient tag, if any
        list($username, $email_domain) = explode('@', strtolower(trim($params['login'])));
        $username .= '+';

        $domain[] = ['login', '=', substr($username, 0, strpos($username, '+')).'@'.$email_domain];
    }
    else {
        $domain[] = ['username', '=', $params['login']];
    }

    $user = User::search($domain)
        ->read(['id', 'login', 'username', 'passkey_user_handle'])
        ->first(true);

    if(!$user) {
        throw new Exception('user_not_found', EQ_ERROR_UNKNOWN_OBJECT);
    }

    return $user;
};

/**
 * Action
 */

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

$formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
$allowed_formats = [];
foreach($formats as $format) {
    $is_format_allowed = Setting::get_value('core', 'security', "passkey_format_$format", false);
    if($is_format_allowed) {
        $allowed_formats[] = $format;
    }
}

$webAuthn = new WebAuthn($rp_name, $rp_id, $allowed_formats);

$credential_ids = [];

if(!empty($params['login'])) {
    $user = $getUserFromLoginParam($params);

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

$auth_token = JWT::encode(
    ['challenge' => $webAuthn->getChallenge()->getHex()],
    constant('AUTH_SECRET_KEY')
);

$auth_options->auth_token = $auth_token;

$context->httpResponse()
        ->status(200)
        ->body((array) $auth_options)
        ->send();
