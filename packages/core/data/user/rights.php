<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Retrieve current permission that a user has on a given entity.',
    'params'        => [
        'user' => [
            'type'          => 'string',
            'description'   => 'login (email address) or ID of targeted user.',
            'required'      => true
        ],
        'entity' => [
            'type'          => 'string',
            'description'   => 'Entity on which operation is to be granted.',
            'default'       => '*'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_RIGHTS'],
    'providers'     => ['context', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\access\AccessController  $access
 */
['context' => $context, 'access' => $access] = $providers;

// retrieve targeted user
if(is_numeric($params['user'])) {
    $ids = User::search(['id', '=', $params['user']])->ids();
}
else {
    // retrieve by login
    $ids = User::search(['login', '=', $params['user']])->ids();
}

if(!count($ids)) {
    throw new Exception("unknown_user", EQ_ERROR_UNKNOWN_OBJECT);
}

$user_id = array_shift($ids);
$rights = $access->getUserRights($user_id, $params['entity']);

// convert ACL value to human string
$rights_txt = [];
$operations = [
    EQ_R_CREATE => 'create',
    EQ_R_READ   => 'read',
    EQ_R_WRITE  => 'update',
    EQ_R_DELETE => 'delete',
    EQ_R_MANAGE => 'manage'
];

foreach($operations as $op => $name) {
    if($rights & $op) {
        $rights_txt[] = $name;
    }
}

$result = [
    'user_id'       => $user_id,
    'entity'        => $params['entity'],
    'rights'        => $rights,
    'rights_txt'    => $rights_txt
];

$context
    ->httpResponse()
    ->status(200)
    ->body($result)
    ->send();
