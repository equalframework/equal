<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class CredentialType extends Model {

    public static function getName() {
        return 'Credential Type';
    }

    public static function getDescription() {
        return 'Definition of the structure expected for a credential secret.';
    }

    public static function getColumns(): array {
        return [
            'name' => [
                'type'  => 'alias',
                'alias' => 'code'
            ],

            'code' => [
                'type'        => 'string',
                'usage'       => 'text/plain:64',
                'description' => 'Unique technical code identifying the credential type.',
                'required'    => true
            ],

            'description' => [
                'type'        => 'string',
                'usage'       => 'text/plain',
                'description' => 'Explanation of the credential type and its intended usage.',
                'multilang'   => true
            ],

            'secret_template' => [
                'type'        => 'string',
                'usage'       => 'text/plain',
                'description' => 'Template copied into the secret of a new credential.',
                'required'    => true
            ]

        ];
    }

    public function getUniques(): array {
        return [
            ['code']
        ];
    }
}