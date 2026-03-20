<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class Nonce extends Model {

    public static function getDescription() {
        return 'A nonce is a number used once to prevent replay attacks. It is typically used in security contexts to ensure that a request is unique and has not been reused maliciously.';
    }

    public static function getColumns() {
        return [

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'List of users that are members of the group.'
            ],

            'scope' => [
                'type'              => 'string',
                'description'       => 'Scope of the nonce, indicating where it can be used. This could be a specific action, endpoint, or context in which the nonce is valid.'
            ],

            'hash' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\Permission',
                'foreign_field'     => 'group_id'
            ],

            'expiry' => [
                'type'              => 'datetime',
                'foreign_field'     => 'group_id'
            ],

            'use_count' => [
                'type'              => 'integer',
                'default'           => 0,
                'description'       => 'Number of times this nonce has been used. It is incremented each time the nonce is successfully used.'
            ],

            'use_max' => [
                'type'              => 'integer',
                'default'           => 1,
                'description'       => 'Maximum number of times this nonce can be used before it is considered invalid.'
            ]

        ];
    }

}