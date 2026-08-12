<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\factor\TotpKey;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns the authentication code of the time-based one-time password.',
    'params'        => [
        'id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'core\security\factor\TotpKey',
            'description'       => 'The totpkey to use to generate the authentication code.',
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
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
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


/**
 * Action
 */

$totpkey = TotpKey::id($params['totpkey_id'])
    ->read(['secret', 'algorithm', 'digits', 'period'])
    ->first();

if(!$totpkey) {
    throw new Exception('unknown_totpkey', EQ_ERROR_UNKNOWN_OBJECT);
}

$counter = intdiv(time(), $totpkey['period']);

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

$auth_code = str_pad((string) $otp, $totpkey['digits'], '0', STR_PAD_LEFT);

$context
    ->httpResponse()
    ->body(['auth_code' => $auth_code])
    ->send();
