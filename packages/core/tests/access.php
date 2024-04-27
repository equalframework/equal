<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\http\HttpRequest;
use core\User;
use core\Group;

$providers = eQual::inject(['context', 'orm', 'auth', 'access']);

$tests = [
    '0101' => [
            'description'       =>  "Retrieve Access Controller service from eQual::announce",
            'return'            =>  ['object'],
            'act'               =>  function () {
                    list($params, $providers) = eQual::announce([
                        'providers' => ['equal\access\AccessController']
                    ]);
                    return $providers['equal\access\AccessController'];
                },
            'assert'            =>  function($access) {
                    return ($access instanceof equal\access\AccessController);
                }
        ],

    '0102' => [
            'description'       =>  "Get Access Controller service using a custom registered name.",
            'return'            =>  ['object'],
            'act'               =>  function (){
                    list($params, $providers) = eQual::announce([
                        'providers' => ['@@testAccess' => 'equal\access\AccessController']
                    ]);
                    return $providers['@@testAccess'];
                },
            'assert'            =>  function($access) {
                    return ($access instanceof equal\access\AccessController);
                }
        ],

    '0103' => [
            'description'       =>  "Get Access Controller service using default registered name.",
            'return'            =>  ['object'],
            'act'               =>  function (){
                    list($params, $providers) = eQual::announce([
                        'providers' => ['access']
                    ]);
                    return $providers['access'];
                },
            'assert'            =>  function($access) {
                    return ($access instanceof equal\access\AccessController);
                }
        ],


    // groups assignments

    '0201' => [
                'description'       =>   "Verify assignment with non-existing group.",
                'help'              => "Create a user, check with a non existing group.",
                'return'            => ['integer'],
                'arrange'           => function() use($providers) {
                        $user = User::create(['login' => 'user_test_1@example.com', 'password' => 'abcd1234'])->first();
                        return $user['id'];
                    },
                'act'            => function($user_id) use($providers) {
                        return $user_id;
                    },
                'assert'            => function($user_id) use($providers) {
                        $access = $providers['access'];
                        return !($access->hasGroup('foo_non_existing_group', $user_id) || $access->hasGroup('foo_non_existing_group'));
                    },
                'rollback'          => function() {
                        User::search(['login', '=', 'user_test_1@example.com'])->delete(true);
                    }
        ],

    '0202' => [
            'description'       => "Verify that new user is assigned only to `users` group.",
            'help'              => "Create a user, check with a non existing group.",
            'return'            => ['integer'],
            'arrange'           => function() use($providers) {
                    $user = User::create(['login' => 'user_test_2@example.com', 'password' => 'abcd1234'])->first();
                    return $user['id'];
                },
            'act'            => function($user_id) use($providers) {
                    return $user_id;
                },
            'assert'            => function($user_id) use($providers) {
                    $access = $providers['access'];
                    return ($access->hasGroup('users', $user_id) && !$access->hasGroup('foo_non_existing_group', $user_id));
                },
            'rollback'          => function() {
                    User::search(['login', '=', 'user_test_2@example.com'])->delete(true);
                }
        ],

    '0203' => [
            'description'       => "Ensure that a user can be added to a group.",
            'help'              => "Create a user, assign it to a group, and check the group membership  of the user.",
            'return'            => ['array'],
            'arrange'           => function() use($providers) {
                    $user = User::create(['login' => 'user_test_3@example.com', 'password' => 'abcd1234'])->first();
                    $group = Group::create(['name' => 'test1'])->first();
                    return [$user['id'], $group['id']];
                },
            'act'               => function ($data) use($providers) {
                    list($user_id, $group_id) = $data;
                    $access = $providers['access'];
                    $access->addGroup($group_id, $user_id);
                    return [$user_id, $group_id];
                },
            'assert'            => function($data) use($providers) {
                    list($user_id, $group_id) = $data;
                    $access = $providers['access'];
                    $users_ids = $access->getGroupUsers($group_id);
                    return $access->hasGroup($group_id, $user_id) && in_array($user_id, $users_ids);
                },
            'rollback'          => function() {
                    Group::search(['name', '=', 'test1'])->delete(true);
                    User::search(['login', '=', 'user_test_3@example.com'])->delete(true);
                }
        ],

    '0204' => [
            'description'       => "Try to get users of a non-existing group.",
            'help'              => "Create a user, assign it to a group, and check the group membership  of the user.",
            'assert'            => function() use($providers) {
                    $access = $providers['access'];
                    $users_ids = $access->getGroupUsers(0);
                    return !count($users_ids);
                }
        ],

    // rights management
    '0300' => [
            'description'       => "Check root user rights.",
            'assert'            => function() use($providers) {
                    $access = $providers['access'];
                    return $access->hasRight(QN_ROOT_USER_ID, EQ_R_MANAGE, 'core\User');
                },
        ],

    '0301' => [
            'description'       => "Re-Check root user rights (to ensure using the rights cache).",
            'assert'            => function() use($providers) {
                    $access = $providers['access'];
                    return $access->hasRight(QN_ROOT_USER_ID, EQ_R_MANAGE, 'core\User');
                },
        ],

    '0303' => [
            'description'       => "Check if a user has a right on its own object.",
            'help'              => "Create a user, and check if it has the resulting user has the MANAGE right on itself.",
            'arrange'           => function() use($providers) {
                    $user = User::create(['login' => 'user_test_4@example.com', 'password' => 'abcd1234'])->first();
                    return $user['id'];
            },
            'assert'            => function($user_id) use($providers) {
                    $access = $providers['access'];
                    return $access->hasRight($user_id, EQ_R_WRITE, 'core\User', $user_id);
                },
            'rollback'          => function() {
                    User::search(['login', '=', 'user_test_4@example.com'])->delete(true);
                }
        ],

    '0304' => [
            'description'       => "Ensure a user is granted rights from its groups.",
            'help'              => "Create a user, assign it to a group, grant some rights to that group and check the resulting rights of the user.",
            'arrange'           => function() use($providers) {
                    $access = $providers['access'];
                    $user = User::create(['login' => 'user_test_5@example.com', 'password' => 'abcd1234'])->first();
                    $group = Group::create(['name' => 'test2'])->first();
                    $access->addGroup($group['id'], $user['id']);
                    return $group['id'];
                },
            'act'               => function ($group_id) use($providers) {
                    $access = $providers['access'];
                    $access->grantGroups($group_id, EQ_R_READ|EQ_R_WRITE|EQ_R_MANAGE, '*');
                    return $group_id;
                },
            'assert'            => function($group_id) use($providers) {
                    $access = $providers['access'];
                    $user = User::search(['login', '=', 'user_test_5@example.com'])->read(['id'])->first();
                    return $access->hasRight($user['id'], EQ_R_READ|EQ_R_WRITE|EQ_R_MANAGE, '*');
                },
            'rollback'          => function() {
                    Group::search(['name', '=', 'test2'])->delete(true);
                    User::search(['login', '=', 'user_test_5@example.com'])->delete(true);
                }
        ],

    '0305' => [
            'description'       => "Ensure a user is revoked rights when its groups are.",
            'help'              => "Create a user, assign it to a group, grant some rights to that group, then remove back those rights, and check the resulting rights of the user.",
            'arrange'           => function() use($providers) {
                    $access = $providers['access'];
                    $user = User::create(['login' => 'user_test_6@example.com', 'password' => 'abcd1234'])->first();
                    $group = Group::create(['name' => 'test3'])->first();
                    $access->grantGroups($group['id'], EQ_R_MANAGE, '*');
                    $access->addGroup($group['id'], $user['id']);
                    return [$user['id'], $group['id']];
                },
            'act'               => function ($data) use($providers) {
                    list($user_id, $group_id) = $data;
                    $access = $providers['access'];
                    $access->revokeGroups($group_id, EQ_R_MANAGE, '*');
                    return $data;
                },
            'assert'            => function($data) use($providers) {
                    list($user_id, $group_id) = $data;
                    $access = $providers['access'];
                    return !$access->hasRight($user_id, EQ_R_MANAGE, 'core\Task');
                },
            'rollback'          => function() {
                    Group::search(['name', '=', 'test3'])->delete(true);
                    User::search(['login', '=', 'user_test_6@example.com'])->delete(true);
                }
        ],


  '0401' => [
            'description'       => "Check if an action is authorized.",
            'help'              => "Create a user, and check if the user can perform an action on its own object.",
            'arrange'           => function() use($providers) {
                    $user = User::create(['login' => 'user_test_7@example.com', 'password' => 'abcd1234'])->first();
                    return $user['id'];
                },
            'assert'            => function($user_id) use($providers) {
                    $access = $providers['access'];
                    return !boolval(count($access->canPerform($user_id, 'validate', 'core\User', $user_id)));
                },
            'rollback'          => function() {
                    User::search(['login', '=', 'user_test_7@example.com'])->delete(true);
                }
        ]
];
