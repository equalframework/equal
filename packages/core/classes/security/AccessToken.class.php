<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\security;

use equal\orm\Model;

class AccessToken extends Model {

    public static function getDescription() {
        return 'Access Token.';
    }

    public static function getColumns() {
        return [
            'jti' => [
                'type'              => 'alias',
                'alias'             => 'id'
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'required'          => true,
                'description'       => 'User associated with the token.'
            ],

            'token_type' => [
                'type'              => 'string',
                'selection'         => [
                    'access_token',
                    'refresh_token'
                ],
                'description'       => 'Type of token (access or refresh).'
            ],

            'expiry' => [
                'type'              => 'datetime',
                'description'       => 'Token expiration date.'
            ],

            'is_revoked' => [
                'type'              => 'boolean',
                'default'           => false,
                'description'       => 'Indicates if the token has been revoked.'
            ],

        ];
    }

}
