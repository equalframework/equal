<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\security\factor;

use core\security\AuthenticationFactor;

class EmailOtpKey extends AuthenticationFactor {

    public static function getDescription(): string {
        return 'A user Email otp key that allows MFA authentication.';
    }

    public static function getColumns(): array {
        return [

            'type' => [
                'type'              => 'string',
                'description'       => 'Type of authentication factor.',
                'help'              => 'Technical mechanism used by the factor: blocked on `email_otp_key` according to current class.',
                'readonly'          => true,
                'default'           => 'email_otp_key'
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'active',
                    'revoked'
                ],
                'description'       => 'Current status of the email OTP.',
                'help'              => 'An email OTP should be revoked after a successful use.',
                'default'           => 'active'
            ],

            'code_hash' => [
                'type'              => 'string',
                'description'       => 'Hash of the temporary code sent by email.',
                'readonly'          => true
            ],

            'code_expires_at' => [
                'type'              => 'datetime',
                'description'       => 'Time after which the code is no longer valid.'
            ],

            'failed_attempts' => [
                'type'              => 'integer',
                'description'       => 'Number of wrong guesses for the code.',
                'default'           => 0
            ]

        ];
    }

    public static function getWorkflow(): array {
        return [
            'active' => [
                'description' => 'The email OTP can be used as authentication method.',
                'transitions' => [
                    'revoke' => [
                        'description'   => 'Revoke permanently the email OTP.',
                        'status'        => 'revoked',
                        'onafter'       => 'onafterRevoke',
                        'policies'      => ['revocable']
                    ]
                ]
            ],
            'revoked' => [
                'description' => 'The email OTP is permanently disabled, it should after each successful use.'
            ]
        ];
    }
}
