<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security\factor;

use core\security\AuthenticationFactor;

class PasswordCredential extends AuthenticationFactor {

    public static function getDescription(): string {
        return 'A password credential that allows a user to authenticate with a memorized secret.';
    }

    public static function getColumns(): array {
        return [

            'type' => [
                'type'              => 'string',
                'description'       => 'Type of authentication factor.',
                'help'              => 'Technical mechanism used by the factor: fixed to `password` for this class.',
                'readonly'          => true,
                'default'           => 'password'
            ],

            'password_hash' => [
                'type'              => 'string',
                'usage'             => 'password',
                'description'       => 'One-way hash used to verify the user password.',
                'help'              => 'Stores a password verifier and never the clear-text password.',
                'required'          => true,
                'readonly'          => true
            ]

        ];
    }
}
