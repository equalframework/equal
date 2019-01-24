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
    'providers'     => ['context', 'auth']
]);


list($context, $am) = [$providers['context'], $providers['auth']];



$user_id = $am->userId();
if($user_id <= 0) {
    throw new Exception($login, QN_ERROR_INVALID_USER);    
}

$collection = User::search(['id', '=', $user_id]);

if(!count($collection->ids())) {
    throw new Exception($login, QN_ERROR_INVALID_USER);
}

$user = $collection->read(['login', 'firstname', 'lastname', 'language'])->adapt('txt')->get(true);

    
$context->httpResponse()
        ->body($data)
        ->send();