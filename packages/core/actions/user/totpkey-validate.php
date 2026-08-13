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
    'constants'     => ['AUTH_SECRET_KEY'],
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

    if($payload['type'] !== 'mfa_challenge' || $payload['amr'] !== 'pwd') {
        throw new Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
    }

    if((int) $payload['iat'] > $now || (int) $payload['exp'] < $now) {
        throw new Exception('expired_token', EQ_ERROR_INVALID_PARAM);
    }

    return $payload['sub'];
};

$base32Decode = function(string $encoded): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $encoded = strtoupper(rtrim($encoded, '='));
    $buffer = 0;
    $bitsLeft = 0;
    $decoded = '';

    foreach (str_split($encoded) as $character) {
        $value = strpos($alphabet, $character);

        if ($value === false) {
            throw new InvalidArgumentException('Invalid Base32 secret');
        }

        $buffer = ($buffer << 5) | $value;
        $bitsLeft += 5;

        if ($bitsLeft >= 8) {
            $bitsLeft -= 8;
            $decoded .= chr(($buffer >> $bitsLeft) & 0xff);
        }
    }

    return $decoded;
};

$getAuthCode = function($totpkey, $timestamp) use($base32Decode) {
    $counter = intdiv($timestamp, $totpkey['period']);

// Encode the counter as an unsigned 64-bit, big-endian integer.
    $counterBytes = pack(
        'N2',
        ($counter >> 32) & 0xffffffff,
        $counter & 0xffffffff
    );

    $hash = hash_hmac(
        $totpkey['algorithm'],
        $counterBytes,
        $base32Decode($totpkey['secret']),
        true
    );

    // Dynamic truncation defined by HOTP/TOTP.
    $offset = ord($hash[strlen($hash) - 1]) & 0x0f;

    $binaryCode =
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff);

    $otp = $binaryCode % (10 ** $totpkey['digits']);

    return str_pad((string) $otp, $totpkey['digits'], '0', STR_PAD_LEFT);
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

$totpkey = TotpKey::search([
    ['id', '=', $params['totpkey_id']],
    ['user_id', '=', $user['id']]
])
    ->read(['status'])
    ->first();

if(!$totpkey) {
    throw new Exception('unknown_totpkey', EQ_ERROR_UNKNOWN_OBJECT);
}

if($totpkey['status'] !== 'pending') {
    throw new Exception('cannot_validate_totpkey', EQ_ERROR_NOT_ALLOWED);
}

$now = time();
$auth_codes = [
    $getAuthCode($totpkey, $now - $totpkey['period']),
    $getAuthCode($totpkey, $now),
    $getAuthCode($totpkey, $now + $totpkey['period'])
];

$auth_code_valid = false;
foreach($auth_codes as $auth_code) {
    if(hash_equals($auth_code, $params['auth_code'])) {
        $auth_code_valid = true;
        break;
    }
}

if(!$auth_code_valid) {
    throw new Exception('auth_code_mismatch', EQ_ERROR_INVALID_PARAM);
}

try {
    TotpKey::id($totpkey['id'])->transition('activate');
}
catch(Exception $e) {
    throw new Exception('totpkey_activation_failed', EQ_ERROR_CONFLICT_OBJECT);
}

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
