<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\http\HttpRequest;
use core\User;
use core\Group;
use core\setting\Setting;

$providers = eQual::inject(['context', 'orm', 'auth', 'access']);

$tests = [
    '40101' => [
            'description'       =>  "Retrieve sub-object using dot notation with ORM::read (with recursion).",
            'act'               =>  function () use ($providers) {
                    $orm = $providers['orm'];
                    $res = $orm->read('core\User', QN_ROOT_USER_ID, ['name', 'groups_ids.name', 'groups_ids.id', 'groups_ids.users_ids.name']);
                    return (is_array($res) && count($res))?reset($res):[];
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
        ],
    '40103' => [
            'description'       =>  "Expand a one2many relation ordered by a nullable many2one field.",
            'act'               =>  function () {
                    $setting = Setting::id(1)
                        ->read(['setting_values_ids' => ['id', 'name']])
                        ->first();

                    return $setting['setting_values_ids']->ids();
                },
            'assert'            =>  function($result) {
                    return in_array(1, $result, true);
                }
        ]
];
