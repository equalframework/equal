<?php
use core\User;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Send emails that are currently in spool queue.",
    'params' 		=>	[
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'spool', 'auth']
]);


// initalise local vars with inputs
list($om, $context, $spool, $auth) = [ $providers['orm'], $providers['context'], $providers['spool'], $providers['auth'] ];

// #todo : improve permissions checks

if( $auth->userId() != ROOT_USER_ID) {
    throw new \Exception("spool_access_restricted", QN_ERROR_NOT_ALLOWED);
}

$spool->run();

$context->httpResponse()
        ->body('')
        ->send();