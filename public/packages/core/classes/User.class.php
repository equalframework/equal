<?php
namespace core;

use qinoa\orm\Model;

class User extends Model {

    public static function getColumns() {
        return [
            'login' => [
                'type'              => 'string'
            ],
            'password' => [
                'type'              => 'string'
            ],
            'firstname' => [
                'type'              => 'string'
            ],
            'lastname' => [
                'type'              => 'string'
            ],
            'language' => [
                'type'              => 'string', 
                'usage'             => 'language/iso-639:2'
            ],
            'validated' => [
                'type'              => 'boolean'
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

    public static function getDefaults() {
        return [
            'validated'    => function() { return false; },
            'language'     => function() { return 'en'; }
        ];
    }

    public static function getConstraints() {
        return [
            'login' =>  [
                'error_message_id'  => 'invalid_login',
                'error_message'     => 'login must be a valid email address',
                'function'          => function ($login) {
                    // valid email address
                    // ... with an exception for admin
                    if($login == 'admin') return true;
                    return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $login, $matches));
                }
            ]
        ];
    }
    
    public static function getUnique() {
        return array(
            ['login']
        );
    }    
}