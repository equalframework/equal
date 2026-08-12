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
        ]
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

$totpkey = TotpKey::id($params['totpkey_id'])
    ->read(['status'])
    ->first();

if(!$totpkey) {
    throw new Exception('unknown_totpkey', EQ_ERROR_UNKNOWN_OBJECT);
}

if($totpkey['status'] !== 'pending') {
    throw new Exception('cannot_validate_totpkey', EQ_ERROR_NOT_ALLOWED);
}

$auth_code = eQual::run('get', 'core_security_TotpKey_auth-code', ['id' => $totpkey['id']]);

if($params['auth_code'] !== $auth_code) {
    throw new Exception('auth_code_mismatch', EQ_ERROR_INVALID_PARAM);
}

TotpKey::id($totpkey['id'])->update(['status' => 'active']);

$context
    ->httpResponse()
    ->send();
