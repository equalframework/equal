<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = announce([
    'description'   => 'Returns descriptor of current User, based on received access_token',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);


list($context, $om, $am) = [$providers['context'], $providers['orm'], $providers['auth']];
// retrieve current User identifier (HTTP headers lookup through Authentication Manager)

$user_id = $am->userId();

if($user_id <= 0) {
    throw new Exception('user unknown', QN_ERROR_INVALID_USER);    
}
// request directly the mapper to bypass permission check on User class 
$ids = $om->search('core\User', ['id', '=', $user_id]);
// make sure the User object is available
if(!count($ids)) {
    throw new Exception('unexpected error', QN_ERROR_INVALID_USER);
}
// user has allways READ right on its own object
$user = User::ids($ids)
            ->read(['login', 'firstname', 'lastname', 'language'])
            ->adapt('txt')
            ->first();
// send back basic info of the User object   
$context->httpResponse()
        ->body($user)
        ->send();