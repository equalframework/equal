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
    'description'   => 'List all groups of a given user.',
    'params'        => [
        'user' => [
            'type'          => 'string',
            'description'   => 'Login (email address) or ID of targeted user.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\access\AccessController  $ac
 */
['context' => $context, 'access' => $ac] = $providers;


// retrieve targeted user
if(is_numeric($params['user'])) {
    $ids = User::search(['id', '=', $params['user']])->ids();
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
}

$user_id = array_shift($ids);

$groups_ids = $ac->getUserGroups($user_id);

$groups = Group::ids($groups_ids)->read(['id', 'name'])->get();

$groups_names = array_map(function($a) {return $a['name'];}, $groups);

$context
    ->httpResponse()
    ->status(200)
    ->body($groups_names)
    ->send();
