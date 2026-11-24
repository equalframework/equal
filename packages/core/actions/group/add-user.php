<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Group;
use core\User;


[$params, $providers] = eQual::announce([
    'description'   => 'Add a user to a given group (identified by name or id).',
    'params'        => [
        'group' => [
            'type'          => 'string',
            'description'   => 'name or ID of targeted group.',
            'required'      => true
        ],
        'user' => [
            'type'          => 'string',
            'description'   => 'login (email address) or ID of targeted user.',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;


// 1) retrieve targeted group

if(is_numeric($params['group'])) {
    // retrieve by id
    $group_id = $params['group'];

    $ids = Group::search(['id', '=', $group_id])->ids();
    if(!count($ids)) {
        throw new \Exception("unknown_group_id", EQ_ERROR_UNKNOWN_OBJECT);
    }
}
else {
    // retrieve by name
    $ids = Group::search(['name', '=', $params['group']])->ids();

    if(!count($ids)) {
        throw new \Exception("unknown_group_name", EQ_ERROR_UNKNOWN_OBJECT);
    }

    $group_id = array_shift($ids);
}

// 2) retrieve targeted user

if(is_numeric($params['user'])) {
    // retrieve by id
    $user_id = $params['user'];

    $ids = User::search(['id', '=', $user_id])->ids();
    if(!count($ids)) {
        throw new \Exception("unknown_user_id", EQ_ERROR_UNKNOWN_OBJECT);
    }
}
else {
    // retrieve by login
    $ids = User::search(['login', '=', $params['user']])->ids();

    if(!count($ids)) {
        throw new \Exception("unknown_username", EQ_ERROR_UNKNOWN_OBJECT);
    }

    $user_id = array_shift($ids);
}

// #memo - no need to switch to root user: this script is private (meant for CLI only)
Group::id($group_id)->update(['users_ids' => [$user_id]]);

$context
    ->httpResponse()
    ->status(204)
    ->send();