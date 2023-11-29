<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Group;

list($params, $providers) = announce([
    'description'   => 'Revoke privilege from a given group.',
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
        'right' =>  [
            'description'   => 'Operation to be revoked.',
            'type'          => 'string',
            'in'            => ['create','read','update','delete','manage'],
            'required'      => true
        ],
        'entity' =>  [
            'description'   => 'Entity on which operation is to be revoked.',
            'type'          => 'string',
            'default'       => '*'
        ]
    ],
    'access'        => [
        'visibility'        => 'private'
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

// retrieve targeted group

if(is_numeric($params['group'])) {
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

$ac->revokeGroups($group_id, $operations[$params['right']], $params['entity']);


// #removed - acl might have been deleted in the process
// $acl_ids = $orm->search('core\Permission', [ ['class_name', '=', $params['entity']], ['group_id', '=', $group_id] ]);
// $acls = $orm->read('core\Permission', $acl_ids, ['group_id', 'class_name', 'rights', 'rights_txt']);

$context->httpResponse()
        ->status(204)
        ->body('')
        ->send();