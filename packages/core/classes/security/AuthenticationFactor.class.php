<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class AuthenticationFactor extends Model {

    public static function getDescription(): string {
        return 'Authentication factor associated with a user account.';
    }

    public static function getColumns(): array {
        return [

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User who owns this authentication factor.',
                'help'              => 'Links the authentication factor to a user account.',
                'required'          => true
            ],

            'type' => [
                'type'              => 'string',
                'selection'         => [
                    'passkey',
                    'totp',
                    'recovery_code'
                ],
                'description'       => 'Type of authentication factor.',
                'help'              => 'Identifies the technical mechanism used by the factor.',
                'required'          => true
            ],

            'label' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'User-facing label for the authentication factor.',
                'help'              => 'Helps the user distinguish several factors of the same type.'
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'pending',
                    'active',
                    'disabled',
                    'revoked'
                ],
                'description'       => 'Current status of the authentication factor.',
                'help'              => 'Controls whether the factor can currently be used for authentication.',
                'default'           => 'active'
            ],

            'confirmed_at' => [
                'type'              => 'datetime',
                'description'       => 'Date and time when the authentication factor was confirmed.',
                'help'              => 'Empty until the factor has been validated and activated.'
            ],

            'last_used_at' => [
                'type'              => 'datetime',
                'description'       => 'Date and time when the authentication factor was last used.',
                'help'              => 'Tracks the latest successful authentication using this factor.'
            ],

            'revoked_at' => [
                'type'              => 'datetime',
                'description'       => 'Date and time when the authentication factor was revoked.',
                'help'              => 'Empty until the factor is permanently revoked.'
            ],

            'revoked_reason' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'Reason why the authentication factor was revoked.',
                'help'              => 'Stores an optional explanation for audit or support purposes.'
            ]

        ];
    }

    public function getIndexes(): array {
        return [
            ['user_id'],
            ['type'],
            ['status']
        ];
    }
}
