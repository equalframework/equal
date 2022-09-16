<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

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
    'create'    =>  QN_R_CREATE,
    'read'      =>  QN_R_READ,
    'update'    =>  QN_R_WRITE,
    'delete'    =>  QN_R_DELETE,
    'manage'    =>  QN_R_MANAGE
];

if(!$ac->isAllowed(QN_R_MANAGE, $operation, $params['entity'])) {
    throw new \Exception('MANAGE,'.$params['entity'], QN_ERROR_NOT_ALLOWED);
}

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

if($rights & QN_R_CREATE)   $rights_txt[] = 'create';
if($rights & QN_R_READ)     $rights_txt[] = 'read';
if($rights & QN_R_WRITE)    $rights_txt[] = 'write';
if($rights & QN_R_DELETE)   $rights_txt[] = 'delete';
if($rights & QN_R_MANAGE)   $rights_txt[] = 'manage';

$context->httpResponse()
        ->status(200)
        ->body(['result' => implode(', ', $rights_txt)])
        ->send();