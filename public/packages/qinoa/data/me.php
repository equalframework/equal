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
    throw new Exception('user unknown', QN_ERROR_INVALID_USER);    
}
die('ok2');
$collection = User::search(['id', '=', $user_id]);
die('ok3');
if(!count($collection->ids())) {
    throw new Exception('unexpected error', QN_ERROR_INVALID_USER);
}
die('ok4');
$user = $collection->read(['login', 'firstname', 'lastname', 'language'])->adapt('txt')->first();
die('ok5');
    
$context->httpResponse()
        ->body($user)
        ->send();