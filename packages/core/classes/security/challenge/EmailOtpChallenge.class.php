<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\security\challenge;

use equal\orm\Model;

class EmailOtpChallenge extends Model {

    public static function getName(): string {
        return 'Email OTP challenge';
    }

    public static function getDescription(): string {
        return 'A temporary challenge used to verify a code sent to a user by email.';
    }

    public static function getColumns(): array {
        return [

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User for whom the email OTP challenge was issued.',
                'required'          => true
            ],

            'code_hash' => [
                'type'              => 'string',
                'description'       => 'Hash of the temporary code sent by email.',
                'required'          => true,
                'readonly'          => true
            ],

            'expires_at' => [
                'type'              => 'datetime',
                'description'       => 'Time after which the challenge can no longer be completed.',
                'required'          => true,
                'readonly'          => true
            ],

            'failed_attempts' => [
                'type'              => 'integer',
                'description'       => 'Number of unsuccessful attempts to complete the challenge.',
                'default'           => 0
            ],

            'status' => [
                'type'              => 'string',
                'usage'             => 'text/plain:25',
                'selection'         => [
                    'pending',
                    'consumed',
                    'invalidated'
                ],
                'description'       => 'Current lifecycle status of the email OTP challenge.',
                'default'           => 'pending'
            ],

            'consumed_at' => [
                'type'              => 'datetime',
                'description'       => 'Date and time when the challenge was successfully completed.'
            ],

            'invalidated_at' => [
                'type'              => 'datetime',
                'description'       => 'Date and time when the challenge was invalidated.'
            ],

            'invalidated_reason' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'Reason why the challenge was invalidated.'
            ]

        ];
    }

    public function getIndexes(): array {
        return [
            ['user_id', 'status'],
            ['expires_at'],
            ['created']
        ];
    }

    public static function getWorkflow(): array {
        return [
            'pending' => [
                'description' => 'The email OTP challenge can be completed.',
                'transitions' => [
                    'consume' => [
                        'description'   => 'Mark the email OTP challenge as successfully completed.',
                        'status'        => 'consumed',
                        'onafter'       => 'onafterConsume'
                    ],
                    'invalidate' => [
                        'description'   => 'Invalidate the email OTP challenge.',
                        'status'        => 'invalidated',
                        'onafter'       => 'onafterInvalidate'
                    ]
                ]
            ],
            'consumed' => [
                'description' => 'The email OTP challenge was successfully completed.'
            ],
            'invalidated' => [
                'description' => 'The email OTP challenge can no longer be completed.'
            ]
        ];
    }

    protected static function onafterConsume($self) {
        $self->update(['consumed_at' => time()]);
    }

    protected static function onafterInvalidate($self) {
        $self->update(['invalidated_at' => time()]);
    }
}
