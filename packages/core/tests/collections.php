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
    '40101' => [
            'description'       =>  "Retrieve sub-object using dot notation with ORM::read (with recursion).",
            'act'               =>  function () use ($providers) {
                    $orm = $providers['orm'];
                    $res = $orm->read('core\User', QN_ROOT_USER_ID, ['name', 'groups_ids.name', 'groups_ids.id', 'groups_ids.users_ids.name']);
                    return ($res > 0 && count($res))?reset($res):[];
                },
            'assert'            =>  function($result) {
                    $res = [];
                    foreach($result['groups_ids.name'] as $gid => $group) {
                        if(!isset($res[$gid])) {
                            $res[$gid] = [];
                        }
                        $res[$gid]['name'] = $group['name'];
                    }
                    foreach($result['groups_ids.users_ids.name'] as $gid => $group) {
                        if(!isset($res[$gid])) {
                            $res[$gid] = [];
                        }
                        foreach($group['users_ids.name'] as $uid => $user) {
                            if(!isset($res[$gid]['users_ids'])) {
                                $res[$gid]['users_ids'] = [];
                            }
                            $res[$gid]['users_ids'][$uid] = $user['name'];
                        }
                    }
                    return ($res[1]['name'] == 'admins' && $res[2]['users_ids'][1] == 'root@equal.local');
                }
        ],
    '40102' => [
            'description'       =>  "Check the return value of ::first() on an empty collection initialized with `id(\$non_existing_id)`.",
            'act'               =>  function () use ($providers) {
                    return User::id(9999999)->first();
                },
            'assert'            =>  function($result) {
                    return $result === null;
                }
        ]
];