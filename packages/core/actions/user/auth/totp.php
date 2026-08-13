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
    'description'	=>	"Attempts to log a user in.",
    'params' 		=>	[
        'login'		=>	[
            'description'   => "The user name, username or login.",
            'type'          => 'string',
            'required'      => true
        ],
        'auth_token' =>  [
            'description'   => "The temporary token that proves the correct password was given.",
            'type'          => 'string',
            'required'      => true
        ],
        'auth_code' => [
            'description'   => "The code given by the user's authenticator application.",
            'type'          => 'string',
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
    'providers'     => ['context', 'auth'],
    'constants'     => ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

/**
 * Methods
 */

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

// we might have received either a login (email) or a username

// if provided login is an email address, attempt to resolve by login
if(strpos($params['login'], '@') > 0) {
    // cleanup provided email (as login): strip heading and trailing spaces and remove recipient tag, if any
    list($username, $domain) = explode('@', strtolower(trim($params['login'])));
    $username .= '+';
    $login = substr($username, 0, strpos($username, '+')).'@'.$domain;
}
// other format: attempt to resolve through username
else {
    // find a user that matches the given username (there should be only one)
    $user = User::search(['username', '=', $params['login']])->read(['login'])->first();
    if(!$user) {
        throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
    }
    $login = $user['login'];
}

// find the user related to the normalized login
$user = User::search(['login', '=', $login])
    ->read(['validated'])
    ->first(true);

if(!$user) {
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

if(!$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

$global_totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled');
$totp_enabled = Setting::get_value('core', 'security', 'auth.totp.enabled', $global_totp_enabled, ['user_id' => $user['id']]);

if(!$totp_enabled) {
    throw new Exception("totp_auth_disabled", EQ_ERROR_NOT_ALLOWED);
}

try {
    $check = $auth->verifyToken($params['auth_token'], constant('AUTH_SECRET_KEY'));
}
catch(Exception $e) {
    $check = false;
}

if($check === false || $check <= 0) {
    throw new Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
}

$token = $auth->decodeToken($params['auth_token']);

$payload = $token['payload'] ?? null;
$now = time();

if($payload['type'] !== 'mfa_challenge' || $payload['amr'] !== 'pwd' || $payload['sub'] !== $user['id']) {
    throw new Exception('invalid_token', EQ_ERROR_INVALID_PARAM);
}

if((int) $payload['iat'] > $now || (int) $payload['exp'] < $now) {
    throw new Exception('expired_token', EQ_ERROR_INVALID_PARAM);
}

$totpkey = TotpKey::search([
    ['user_id', '=', $user['id']],
    ['type', '=', 'totp'],
    ['status', '=', 'active']
])
    ->read(['secret', 'algorithm', 'digits', 'period', 'failed_attempts'])
    ->first();

if(!$totpkey) {
    throw new Exception('totpkey_not_found', EQ_ERROR_NOT_ALLOWED);
}

if($totpkey['failed_attempts'] > 5) {
    throw new Exception('allowed_failed_attempts_reached', EQ_ERROR_NOT_ALLOWED);
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
    TotpKey::id($totpkey['id'])->update([
        'failed_attempts' => $totpkey['failed_attempts'] + 1
    ]);

    throw new Exception('auth_code_mismatch', EQ_ERROR_INVALID_PARAM);
}

TotpKey::id($totpkey['id'])->update(['last_used_at' => time()]);

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
