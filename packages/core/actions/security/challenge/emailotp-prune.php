<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\challenge\EmailOtpChallenge;
use core\setting\Setting;

[$params, $providers] = eQual::announce([
    'description'   => 'Prunes email OTP challenges that have exceeded the configured retention duration.',
    'params'        => [
        'user_id' => [
            'type'        => 'integer',
            'description' => 'Optional user identifier to limit the purge to a single user.',
            'min'         => 0,
            'default'     => 0
        ]
    ],
    'access'        => [
        'visibility' => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var equal\php\Context $context
 */
['context' => $context] = $providers;

$retention_duration = Setting::get_value(
    'core',
    'security',
    'auth.email_otp.retention_duration',
    604800
);

if($retention_duration <= 0) {
    throw new Exception('invalid_email_otp_retention_duration', EQ_ERROR_INVALID_CONFIG);
}

$cutoff = time() - $retention_duration;

EmailOtpChallenge::search([
        [
            ['status', '=', 'consumed'],
            ['consumed_at', '<=', $cutoff]
        ],
        [
            ['status', '=', 'invalidated'],
            ['invalidated_at', '<=', $cutoff]
        ],
        [
            ['status', '=', 'pending'],
            ['expires_at', '<=', $cutoff]
        ]
    ])
    ->delete(true);

$context
    ->httpResponse()
    ->status(204)
    ->send();
