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
                'onchange'          => 'core\User::onchangePassword',
                'required'          => true
            ],

// #todo : deprecate firstname and lastname fields (use only `login` to refer to a user)            
            'firstname' => [
                'type'              => 'string'
            ],

            'lastname' => [
                'type'              => 'string'
            ],

            'language' => [
                'type'              => 'string', 
                'usage'             => 'language/iso-639:2',
                'default'           => 'en'
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
            ]
        ];
    }

    /**
     * Make sure password is crypted when stored to DB.
     * If not crypted yet, password is hashed using CRYPT_BLOWFISH algorithm.
     * (This has to be done after password assign, in order to be able to validate the constraints set on password field.)
     */
    public static function onchangePassword($om, $ids, $lang) {
        $values = $om->read(__CLASS__, $ids, ['password'], $lang);
        foreach($values as $oid => $odata) {
            if(substr($odata['password'], 0, 4) != '$2y$') {
                $om->write(__CLASS__, $oid, ['password' => password_hash($odata['password'], PASSWORD_BCRYPT)], $lang);
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
            // #memo : password constraints are set based on the 'usage' attribute
        ];
    }
    
}