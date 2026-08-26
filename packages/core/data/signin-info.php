<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\AuthenticationFactor;
use core\security\factor\TotpKey;
use core\setting\Setting;
use core\setting\SettingValue;
use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns user sign in information.',
    'params'        => [
        'login' =>	[
            'description'   => 'User username or login (email).',
            'type'          => 'string',
            'required'      => true
        ],
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'public'
    ],
    'constants'     => ['DEFAULT_LANG'],
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

/**
 * Cleanups login email: strip spaces, remove recipient tag
 *
 * @param string $email
 * @return string
 */
$cleanUpEmail = function(string $email): string {
    $cleaned_email = strtolower(trim($email));
    if(strpos($cleaned_email, '+') !== false) {
        $cleaned_email = preg_replace('/\+.*@/', '@', $cleaned_email);
    }

    return $cleaned_email;
};

/**
 * Action
 */

$auth_user_id = $auth->userId();

// #memo - $params['login'] is either username or login (email address)
$user_domain = ['username', '=', $params['login']];

if(filter_var($params['login'], FILTER_VALIDATE_EMAIL)) {
    $user_domain = ['login', '=', $cleanUpEmail($params['login'])];
}

$user = User::search($user_domain)
    ->read(['id', 'passkeys_ids', 'username', 'login'])
    ->first(true);

if(is_null($user)) {
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

$allowed_methods = ['pwd'];
$allowed_creations = [];
$auth_method_data = [];

/*
    Password
*/

$global_auth_password_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required');
$auth_password_totp_required = Setting::get_value('core', 'security', 'auth.password.totp_required', $global_auth_password_totp_required, ['user_id' => $user['id']]);

/*
    Passkey
*/

$global_auth_passkey_enabled = Setting::get_value('core', 'security', 'auth.passkey.enabled');
$auth_passkey_enabled = Setting::get_value('core', 'security', 'auth.passkey.enabled', $global_auth_passkey_enabled, ['user_id' => $user['id']]);
if($auth_passkey_enabled) {
    $allowed_methods[] = 'passkey';

    $global_passkey_creation = Setting::get_value('core', 'security', 'auth.passkey.creation');
    $passkey_creation = Setting::get_value('core', 'security', 'auth.passkey.creation', $global_passkey_creation, ['user_id' => $user['id']]);

    if($passkey_creation) {
        $allowed_creations[] = 'passkey';
    }

    $user_handle = Setting::get_value('core', 'security', 'passkey_user-handle', null, ['user_id' => $user['id']]);
    if(!$user_handle) {
        // generate temporary anonymous user_handle
        $user_handle = bin2hex(random_bytes(16));

        $setting = Setting::search(['name', '=', 'core.security.passkey_user-handle'])
            ->read(['id'])
            ->first();

        if($setting) {
            // make sure the handle is not already assigned
            while(true) {
                $values = SettingValue::search([
                        ['setting_id', '=', $setting['id']],
                        ['value', '=', $user_handle]]
                )
                    ->get();

                if(!count($values)) {
                    break;
                }
                $user_handle = bin2hex(random_bytes(16));
            }

            Setting::set_value('core', 'security', 'passkey_user-handle', $user_handle, ['user_id' => $user['id']]);
        }
    }

    $auth_method_data['passkey']['user_handle'] = $user_handle;
}


/*
    Totp key
*/

$global_auth_password_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
$auth_password_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled', $global_auth_password_totp_enabled, ['user_id' => $user['id']]);

if($auth_password_totp_enabled) {
    $auth_method_data['otp']['enabled'] = true;
    if($auth_password_totp_required) {
        $auth_method_data['pwd']['otp_required'] = true;
    }
}

$global_totpkey_creation = Setting::get_value('core', 'security', 'auth.totp.creation');
$totpkey_creation = Setting::get_value('core', 'security', 'auth.totp.creation', $global_totpkey_creation, ['user_id' => $user['id']]);

if($auth_password_totp_enabled && ($totpkey_creation || $auth_password_totp_required)) {
    $allowed_creations[] = 'totpkey';
}

$result = [
    'username'          => trim($params['login']),
    'allowed_methods'   => $allowed_methods,
    'allowed_creations' => $allowed_creations,
    'methods_data'      => $auth_method_data,
    'user_data'         => [
        'has_passkey' => false,
        'has_totpkey' => false
    ]
];

$auth_factors = AuthenticationFactor::search([
    ['user_id', '=', $user['id']],
    ['status', '=', 'active']
])
    ->read(['type', 'label'])
    ->get(true);

foreach($auth_factors as $auth_factor) {
    if($auth_factor['type'] === 'passkey') {
        $result['user_data']['has_passkey'] = true;
    }
    if($auth_factor['type'] === 'totp') {
        $result['user_data']['has_totpkey'] = true;

        $totpkey = TotpKey::id($auth_factor['id'])
            ->read(['digits'])
            ->first();

        $result['methods_data']['otp']['digits'] = $totpkey['digits'];
    }
}

if($user['id'] === $auth_user_id) {
    // add detailed factors only if user is authenticated
    $result['user_data']['factors'] = $auth_factors;
}

$context
    ->httpResponse()
    ->body($result)
    ->send();
