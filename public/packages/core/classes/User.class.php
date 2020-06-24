<?php
namespace core;

use qinoa\orm\Model;

class User extends Model {

    public static function getColumns() {
        return array(
            'login'			=> array('type' => 'string'),
            'password'		=> array('type' => 'string'),
            'firstname'		=> array('type' => 'string'),
            'lastname'		=> array('type' => 'string'),
            'language'		=> array('type' => 'string', 'usage' => 'language/iso-639:2'),
            'validated'		=> array('type' => 'boolean'),
            'groups_ids'	=> array('type' => 'many2many', 
                                'foreign_object'    => 'core\Group', 
                                'foreign_field'     => 'users_ids', 
                                'rel_table'         => 'core_rel_group_user', 
                                'rel_foreign_key'   => 'group_id', 
                                'rel_local_key'     => 'user_id'),
        );
    }

    public static function getDefaults() {
        return [
            'validated'    => function() { return false; },
            'language'     => function() { return 'en'; }
        ];
    }

    public static function getConstraints() {
        return [
            'login'			=>  [
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