<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Group;

[$params, $providers] = eQual::announce([
    'description'   => 'Revoke privilege from a given group.',
    'params'        => [
        'group' => [
            'type'          => 'string',
            'description'   => 'name or ID of targeted group.',
            'required'      => true
        ],
        'right' => [
            'type'          => 'string',
            'description'   => 'Operation to be revoked.',
            'in'            => ['create','read','update','delete','manage'],
            'required'      => true
        ],
        'entity' => [
            'type'          => 'string',
            'description'   => 'Entity on which operation is to be revoked.',
            'default'       => '*'
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
    'providers'     => ['context', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\access\AccessController  $ac
 */
['context' => $context, 'access' => $ac] = $providers;


$operations = [
    'create'    =>  EQ_R_CREATE,
    'read'      =>  EQ_R_READ,
    'update'    =>  EQ_R_WRITE,
    'delete'    =>  EQ_R_DELETE,
    'manage'    =>  EQ_R_MANAGE
];

if(!$ac->isAllowed(EQ_R_MANAGE, $params['entity'])) {
    throw new \Exception('MANAGE,'.$params['entity'], EQ_ERROR_NOT_ALLOWED);
}

// retrieve targeted group

if(is_numeric($params['group'])) {
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

$ac->revokeGroups($group_id, $operations[$params['right']], $params['entity']);


// #removed - acl might have been deleted in the process
// $acl_ids = $orm->search('core\Permission', [ ['class_name', '=', $params['entity']], ['group_id', '=', $group_id] ]);
// $acls = $orm->read('core\Permission', $acl_ids, ['group_id', 'class_name', 'rights', 'rights_txt']);

$context
    ->httpResponse()
    ->status(204)
    ->body('')
    ->send();