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

            'fullname' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'function'          => 'calcFullname'
            ],

            // #todo - add a distinct field for 'locale'

            'language' => [
                'type'              => 'string',
                'usage'             => 'language/iso-639',
                'default'           => 'en',
                'description'       => "Preferred locale for user interfaces.",
            ],

            'validated' => [
                'type'              => 'boolean',
                'default'           => false,
                'description'       => 'Flag telling if the User has validated his email address.',
                'help'              => "This fields is used at signin to prevent non-validated user to log in.",
                'onupdate'          => 'onupdateValidated'
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
            ],

            // application logic related status of the object
            'status' => [
                'type'              => 'string',
                // initial status
                'default'           => 'created'
                // list of possible statuses corresponds to the keys of the map returned by `getWorkflow()`
                // onupdate is allowed but it is better practice to use the function properties in the workflow descriptor
            ]
        ];
    }

    public static function getWorkflow() {
        return [
            'created' => [
                'transitions' => [
                    'validation' => [
                        'depends_on'  => ['validated'],
                        'domain'      => ['validated', '=', true],
                        'description' => 'Update the user status based on the `validated` field.',
                        'help'        => "The `validated` field is set by a dedicated controller that handles email confirmation requests.",
                        'status'	  => 'validated',
                        'function'    => 'onValidated'
                    ]
                ]
            ],
            'validated' => [
                'transitions' => [
                    'suspension' => [
                        'description' => 'Set the user status as suspended.',
                        'status'	  => 'suspended'
                    ],
                    'confirmation' => [
                        'domain'      => ['validated', '=', true],
                        'description' => 'Update the user status based on the `confirmed` field.',
                        'help'        => "The `confirmed` field is set by a dedicated controller that handles the account confirmation process (auto or manual).",
                        'status'	  => 'confirmed'
                    ]
                ]
            ],
            'confirmed' => [
                'transitions' => [
                    'suspension' => [
                        'description' => 'Set the user account as disabled (prevents signin).',
                        'status'	  => 'suspended'
                    ]
                ]
            ],
            'suspended' => [
                'transitions' => [
                    'confirmation' => [
                        'description' => 'Re-enable the user account.',
                        'status'	  => 'confirmed'
                    ]
                ]
            ]
        ];
    }

    /**
     * Filter method for password updates.
     * Make sure password is encrypted when stored to DB.
     * If not encrypted yet, password is hashed using CRYPT_BLOWFISH algorithm.
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
                        return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $login));
                    }
                ]
            ]
            // #memo - password constraints are applied based on the 'usage' attribute
        ];
    }

    /**
     * Compute the value of the fullname of the user (concatenate firstname and lastname).
     *
     * @param \equal\orm\Collection $self  An instance of a User collection.
     *
     */
    public static function calcFullname($self) {
        $result = [];
        $self->read(['firstname', 'lastname']);
        foreach($self as $id => $user) {
            $result[$id] = $user['firstname'].' '.$user['lastname'];
        }
        return $result;
    }

    public static function onupdate($self) {

    }

    public static function onupdateValidated($self) {
        $self->read(['id', 'name']);
        foreach($self as $id => $object) {
            $object['name'] = 'testtralala';
        }
    }

    public static function onValidated($orm, $ids) {
        return $orm->transition(self::getType(), $ids, 'confirmation');
    }

    public static function onchange($event, $values) {
        $result = [];

        if(isset($event['firstname']) || isset($event['lastname'])) {

            if(isset($event['firstname'])) {
                if(isset($event['lastname'])) {
                    $result['fullname'] = $event['firstname'].' '.$event['lastname'];
                }
                else {
                    if(isset($values['lastname'])) {
                        $result['fullname'] = $event['firstname'].' '.$values['lastname'];
                    }
                    else {
                        $result['fullname'] = $event['firstname'];
                    }
                }
            }
            else {
                if(isset($values['firstname'])) {
                    $result['fullname'] = $values['firstname'].' '.$event['lastname'];
                }
                else {
                    $result['fullname'] = $event['lastname'];
                }
            }
        }

        return $result;
    }
}