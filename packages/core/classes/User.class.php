<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class User extends Model {

    public static function getName() {
        return 'User';
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'login'
            ],

            'login' => [
                'type'              => 'string',
                'usage'             => 'email',
                'required'          => true,
                'unique'            => true
            ],

            'password' => [
                'type'              => 'string',
                'usage'             => 'password',
                'onupdate'          => 'onupdatePassword',
                'required'          => true
            ],

            'firstname' => [
                'type'              => 'string'
            ],

            'lastname' => [
                'type'              => 'string'
            ],

            // #todo - rename to 'locale'
            'language' => [
                'type'              => 'string',
                'usage'             => 'language/iso-639',
                'default'           => 'en',
                'description'       => "Preferred locale for user interfaces.",
            ],

            'validated' => [
                'type'              => 'boolean',
                'default'           => false,
                'description'       => 'Flag telling if the User has validated its email address and can sign in.'
            ],

            'groups_ids' => [
                'type'              => 'many2many',
                'foreign_object'    => 'core\Group',
                'foreign_field'     => 'users_ids',
                'rel_table'         => 'core_rel_group_user',
                'rel_foreign_key'   => 'group_id',
                'rel_local_key'     => 'user_id'
            ],

            'permissions_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\Permission',
                'foreign_field'     => 'user_id'
            ],

            'setting_values_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\SettingValue',
                'foreign_field'     => 'user_id',
                'description'       => 'List of settings that relate to the user.'
            ]

        ];
    }

    /**
     * Filter method for password updates.
     * Make sure password is crypted when stored to DB.
     * If not crypted yet, password is hashed using CRYPT_BLOWFISH algorithm.
     * (This has to be done after password assign, in order to be able to validate the constraints set on password field.)
     *
     * @param   $om     Object  Instance of the ObjectManager Service
     * @param   $ids    array   List of User objects identifiers
     * @param   $lang   string  Language for multilang fields
     */
    public static function onupdatePassword($om, $ids, $values, $lang) {
        $values = $om->read(self::getType(), $ids, ['password']);
        foreach($values as $oid => $odata) {
            if(substr($odata['password'], 0, 4) != '$2y$') {
                $om->update(self::getType(), $oid, ['password' => password_hash($odata['password'], PASSWORD_BCRYPT)]);
            }
        }
    }

    public static function getConstraints() {
        return [
            'login' =>  [
                'invalid_email' => [
                    'message'       => 'Login must be a valid email address.',
                    'function'      => function ($login, $values) {
                        // an exception for admin
                        if($login == 'admin') return true;
                        return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $login));
                    }
                ]
            ]
            // #memo - password constraints are applied based on the 'usage' attribute
        ];
    }

}