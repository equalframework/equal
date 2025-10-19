<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Passkey extends Model {

    public static function getDescription(): string {
        return 'A user Passkey that allows password less authentication.';
    }

    public static function getColumns(): array {
        return [

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User who owns this passkey.',
                'help'              => 'Links the passkey to a user account.',
                'required'          => true
            ],

            'credential_id' => [
                'type'              => 'string',
                'description'       => 'User credential id.',
                'help'              => 'It allows to select the right public key depending on which credential the user choose to authenticate.',
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
                'default'           => 0,
                'min'               => 0
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

    public function getUnique(): array {
        return [
            ['user_id', 'credential_id']
        ];
    }
}
