<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Group;
use core\User;


list($params, $providers) = announce([
    'description'   => 'Add a user to a given group (identified by name or id).',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'group' =>  [
            'description'   => 'name or ID of targeted group.',
            'type'          => 'string',
            'required'      => true
        ],
        'user' =>  [
            'description'   => 'login (email address) or ID of targeted user.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'        => 'private'
    ],
    'providers'     => ['context', 'auth', 'access', 'orm']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\orm\ObjectManager            $orm
 * @var \equal\auth\AuthenticationManager   $am
 * @var \equal\access\AccessController      $ac
 */
list($context, $orm, $am, $ac) = [ $providers['context'], $providers['orm'], $providers['auth'], $providers['access'] ];


// 1) retrieve targeted group

if(is_numeric($params['group'])) {
    // retrieve by id
    $group_id = $params['group'];

    $ids = Group::search(['id', '=', $group_id])->ids();
    if(!count($ids)) {
        throw new \Exception("unknown_group_id", QN_ERROR_UNKNOWN_OBJECT);
    }
}
else {
    // retrieve by name
    $ids = Group::search(['name', '=', $params['group']])->ids();

    if(!count($ids)) {
        throw new \Exception("unknown_group_name", QN_ERROR_UNKNOWN_OBJECT);
    }

    $group_id = array_shift($ids);
}

// 2) retrieve targeted user

if(is_numeric($params['user'])) {
    // retrieve by id
    $user_id = $params['user'];

    $ids = User::search(['id', '=', $user_id])->ids();
    if(!count($ids)) {
        throw new \Exception("unknown_user_id", QN_ERROR_UNKNOWN_OBJECT);
    }
}
else {
    // retrieve by login
    $ids = User::search(['login', '=', $params['user']])->ids();

    if(!count($ids)) {
        throw new \Exception("unknown_username", QN_ERROR_UNKNOWN_OBJECT);
    }

    $user_id = array_shift($ids);
}

// #memo - no need to switch to root user: this script is private (meant for CLI only)
Group::id($group_id)->update(['users_ids' => [$user_id]]);

$context->httpResponse()
        ->status(204)
        ->send();