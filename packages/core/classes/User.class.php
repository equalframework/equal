<?php
namespace core;

use qinoa\orm\Model;

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
                'required'          => true,
                'unique'            => true                
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
                'usage'             => 'language/iso-639:2',
                'default'           => 'en'
            ],
            'validated' => [
                'type'              => 'boolean',
                'default'           => false
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


    public static function getConstraints() {
        return [
            'login' =>  [
                'invalid_email' => [
                    'message'       => 'Login must be a valid email address.',
                    'function'      => function ($login) {
                        // an exception for admin
                        if($login == 'admin') return true;
                        return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $login));
                    }    
                ]
            ],
            'firstname' =>  [
                'too_short' => [
                    'message'       => 'Firstname must be 2 chars long at minimum.',
                    'function'      => function ($firstname) {
                        return (bool) (strlen($firstname) >= 2);
                    }
                ],
                'invalid_chars' => [
                    'message'       => 'Firstname must contain only naming glyphs.',
                    'function'      => function ($firstname) {
                        return (bool) (preg_match('/^[\w\'\-,.][^0-9_!¡?÷?¿\/\\+=@#$%ˆ&*(){}|~<>;:[\]]{1,}$/u', $firstname));                        
                    }
                ]
            ]

        ];
    }
    
}