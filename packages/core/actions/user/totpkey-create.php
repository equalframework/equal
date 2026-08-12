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
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$user_id = $auth->userId();

if($user_id <= 0) {
    throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
}

$user = User::id($user_id)->first();

if(!$user) {
    throw new Exception('unexpected_error', EQ_ERROR_INVALID_USER);
}

$res_fields = ['algorithm', 'digits', 'period', 'totp_uri', 'totp_qr_code_uri'];

$totpkey = TotpKey::search([
    ['user_id', '=', $user['id']],
    ['type', '=', 'totp'],
    ['status', '=', 'pending']
])
    ->read([$res_fields])
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
        ->read([$res_fields])
        ->first(true);
}

$context
    ->httpResponse()
    ->body($totpkey)
    ->send();
