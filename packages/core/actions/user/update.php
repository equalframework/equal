<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Updates a user account based on given details.',
    'params'        => [
        'id' =>  [
            'description'   => 'Identifier of the user to update.',
            'type'          => 'integer',
            'required'      => true
        ],
        'firstname' =>  [
            'description'   => 'User firstname.',
            'type'          => 'string',
        ],
        'lastname' => [
            'description'   => 'User lastname.',
            'type'          => 'string'
        ],
        'language' => [
            'description'   => 'User language.',
            'type'          => 'string'
        ]
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$user_id = $auth->userId();

if($user_id != EQ_ROOT_USER_ID && $user_id != $params['id']) {
    throw new Exception('not_allowed', EQ_ERROR_NOT_ALLOWED);
}

// update user instance
$collection = User::id($params['id']);

if(!$collection->first()) {
    throw new Exception('unknown_user', EQ_ERROR_INVALID_PARAM);
}

$values = [];

if(isset($params['firstname']) && $params['firstname']) {
    $values['firstname'] = $params['firstname'];
}

if(isset($params['lastname']) && $params['lastname']) {
    $values['lastname'] = $params['lastname'];
}

if(isset($params['language']) && $params['language']) {
    $values['language'] = $params['language'];
}

if(count($values)) {
    $collection->update($values);
}

$user = $collection
    ->adapt('json')
    ->first(true);

$context->httpResponse()
        ->status(200)
        ->body($user)
        ->send();
