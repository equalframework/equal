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
    'access'        => [
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


$operations = [
    'create'    =>  QN_R_CREATE,
    'read'      =>  QN_R_READ,
    'update'    =>  QN_R_WRITE,
    'delete'    =>  QN_R_DELETE,
    'manage'    =>  QN_R_MANAGE
];

if(!$ac->isAllowed(QN_R_MANAGE, $params['entity'])) {
    throw new \Exception('MANAGE,'.$params['entity'], QN_ERROR_NOT_ALLOWED);
}

// 1) retrieve targeted user
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

// 2) grant user for given operation
$ac->grantUsers($user_id, $operations[$params['right']], $params['entity']);

$acl_ids = $orm->search('core\Permission', [ ['class_name', '=', $params['entity']], ['user_id', '=', $user_id] ]);

if($acl_ids < 0 || !count($acl_ids)) {
    throw new \Exception("acl_creation_failed", QN_ERROR_UNKNOWN);
}

$acls = $orm->read('core\Permission', $acl_ids, ['user_id', 'class_name', 'rights', 'rights_txt']);

$context->httpResponse()
        ->body(['result' => array_shift($acls)])
        ->send();
