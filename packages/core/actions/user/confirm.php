<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([	
    'description'	=>	"Validate a user subscription.",
    'params' 		=>	[
        'code' => [
            'description'   => 'Code for athenticating user.',
            'type'          => 'string',
            'usage'         => 'email',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],        
    'providers'     => ['context', 'orm', 'auth']
]);


// initalise local vars with inputs
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];



list($login, $password) = explode(':', base64_decode($params['code']));


// try to create a new user account
$json = run('do', 'user_login', [
            'login'         => $login, 
            'password'      => $password
        ]);


// decode json into an array
$data = json_decode($json, true);

// relay error if any
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) {
        throw new Exception($message, qn_error_code($name));
    }
}

// we received an array describing a User object
$user = $data;

$res = $om->write('core\User', $user['id'], ['validated' => true]);
if($res < 0) {
    throw new Exception('unable to update user', QN_ERROR_UNKNOWN);
}
                 
$context->httpResponse()
        ->body($user)
        ->send();