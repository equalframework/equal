<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security\factor;

use core\security\AuthenticationFactor;

class Passkey extends AuthenticationFactor {

    public static function getDescription(): string {
        return 'A user Passkey that allows passwordless authentication.';
    }

    public static function getColumns(): array {
        return [

            'type' => [
                'type'              => 'string',
                'description'       => 'Type of authentication factor.',
                'help'              => 'Technical mechanism used by the factor: blocked on `passkey` according to current class.',
                'readonly'          => true,
                'default'           => 'passkey'
            ],

            'credential_id' => [
                'type'              => 'string',
                'usage'             => 'text/plain:2048',
                'description'       => 'User credential id.',
                'help'              => 'The credential is stored as hexadecimal string. It allows to select the right public key depending on which credential the user choose to authenticate.',
                'required'          => true
            ],

            'credential_public_key' => [
                'type'              => 'string',
                'description'       => 'User credential public key.',
                'help'              => 'The public key and private key are created by the authenticator.',
                'required'          => true
            ],

            'signature_counter' => [
                'type'              => 'integer',
                'description'       => 'Authenticator usage counter to prevent replay attacks.',
                'help'              => 'It stays at 0 if the authenticator does not handle "signCount", else it\'s incremented by the authenticator at each successful authentication.',
                'default'           => 0
            ],

            'fmt' => [
                'type'              => 'string',
                'description'       => 'Specifies the attestation format used by the authenticator.',
                'help'              => 'The fmt field indicates the method the authenticator uses to prove its legitimacy, helping determine its trust level.
                    Possible values are: tpm, packed, fido-u2f, android-key, none.',
                'required'          => true
            ]

        ];
    }
}
