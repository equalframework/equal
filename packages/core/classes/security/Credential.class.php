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

            'type' => [
                'type'        => 'string',
                'description' => 'Authentication type used by the credential.',
                'required'    => true
            ],

            'secret' => [
                'type'        => 'string',
                'usage'       => 'secret',
                'description' => 'Encrypted credential payload.',
                'readonly'    => true
            ]
        ];
    }

    public static function getOperationPolicies(): array {
        return [
            EQ_R_READ => [
                'secret' => false
            ],

            EQ_R_UPDATE => [
                'secret' => false
            ]
        ];
    }
}
