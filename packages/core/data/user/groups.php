<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;
use core\Group;

list($params, $providers) = announce([
    'description'   => 'Grant additional privilege to given user.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'user' =>  [
            'description'   => 'login (email address) or ID of targeted user.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'auth', 'access', 'orm']
]);

list($context, $orm, $am, $ac) = [ $providers['context'], $providers['orm'], $providers['auth'], $providers['access'] ];


// retrieve targeted user
if(is_numeric($params['user'])) {
    $ids = User::search(['id', '=', $params['user']])->ids();
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
}

$user_id = array_shift($ids);

$groups_ids = $ac->groups($user_id);

$groups = Group::ids($groups_ids)->read(['id', 'name'])->get();

$groups_txt = array_map(function($a) {return $a['name'];}, $groups);

$context->httpResponse()
        ->status(200)
        ->body(['result' => implode(', ', $groups_txt)])
        ->send();