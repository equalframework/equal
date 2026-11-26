<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class Credential extends Model {

    public static function getColumns(): array {
        return [
            'name' => [
                'type'        => 'string',
                'description' => 'Human-readable credential name.',
                'required'    => true
            ],

            'description' => [
                'type'        => 'string',
                'description' => 'Intended usage of the credential.'
            ],

            'credential_type_id' => [
                'type'           => 'many2one',
                'foreign_object' => 'core\\security\\CredentialType',
                'description'    => 'Type defining the expected credential secret format.',
                'required'       => true
            ],

            'secret' => [
                'type'        => 'string',
                'usage'       => 'secret',
                'description' => 'Encrypted credential payload.',
                'default'     => function($values = []) {
                    if(empty($values['credential_type_id'])) {
                        return null;
                    }

                    $credentialType = CredentialType::id($values['credential_type_id'])
                        ->read(['secret_template'])
                        ->first();

                    return $credentialType['secret_template'] ?? null;
                },
                'readonly'    => true
            ]
        ];
    }

    public static function getOperationPolicies(): array {
        return [
            EQ_R_UPDATE => [
                'secret' => ['secret_editable']
            ]
        ];
    }

    public static function getPolicies(): array {
        return [
            'secret_editable' => [
                'description' => 'Checks that the credential secret can be updated.',
                'function'    => 'policySecretEditable'
            ]
        ];
    }

    protected static function policySecretEditable($self): array {
        $result = [];

        $self->read(['state']);

        foreach($self as $id => $credential) {
            if($credential['state'] !== 'draft') {
                $result[$id] = [
                    'secret_not_editable' => 'The credential secret can only be updated while the credential is in draft state.'
                ];
            }
        }

        return $result;
    }
}
