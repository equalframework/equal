<?php
/*
    This file is part of the Qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = eQual::announce([
    'description'   => 'Grant additional privilege to given user.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],    
    'params'        => [
        'login' =>  [
            'description'   => 'email address of targeted user.',
            'type'          => 'string', 
            'required'      => true
        ],
        'right' =>  [
            'description'   => 'Operation to be granted.',
            'type'          => 'string', 
            'in'            => ['create','read','update','delete','manage'],
            'required'      => true
        ],
        'entity' =>  [
            'description'   => 'Entity on which operation is to be granted.',
            'type'          => 'string', 
            'default'       => '*'
        ]        
    ],
    'providers'     => ['context', 'auth', 'access'] 
]);

list($context, $am, $ac) = [ $providers['context'], $providers['auth'], $providers['access'] ];


$operations = [
    'create'    =>  QN_R_CREATE,
    'read'      =>  QN_R_READ,
    'update'    =>  QN_R_WRITE,
    'delete'    =>  QN_R_DELETE,
    'manage'    =>  QN_R_MANAGE
];

if(!isset($operations[$params['right']])) {
    throw new Exception("unknown operation", QN_ERROR_INVALID_PARAM);
}

if(!$ac->isAllowed(QN_R_MANAGE, $operation, $params['entity'])) {
    throw new \Exception('MANAGE,'.$params['entity'], QN_ERROR_NOT_ALLOWED);
}

// retrieve targeted user identifier
$ids = User::search(['login', '=', $params['login']])->ids();
if(!count($ids)) { 
    throw new Exception("no user by that username", QN_ERROR_UNKNOWN_OBJECT);
}

$ac->grantUsers($ids, $operations[$params['right']], $params['entity']);

$context->httpResponse()
        ->status(204)
        ->body('')
        ->send();