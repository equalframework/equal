<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = announce([
    'description'   => 'Retrieve current permission that a user has on a given entity.',
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
        ],
        'entity' =>  [
            'description'   => 'Entity on which operation is to be granted.',
            'type'          => 'string',
            'default'       => '*'
        ]
    ],
    'providers'     => ['context', 'auth', 'access', 'orm']
]);

list($context, $orm, $am, $ac) = [ $providers['context'], $providers['orm'], $providers['auth'], $providers['access'] ];


$operations = [
    QN_R_CREATE => 'create',
    QN_R_READ   => 'read',
    QN_R_WRITE  => 'update',
    QN_R_DELETE => 'delete',
    QN_R_MANAGE => 'manage'
];

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

$rights = $ac->rights($user_id, $params['entity']);

$rights_txt = [];

foreach($operations as $id => $name) {
    if($rights & $id) {
        $rights_txt[] = $name;
    }
}

$context->httpResponse()
        ->status(200)
        ->body(['result' => implode(', ', $rights_txt)])
        ->send();