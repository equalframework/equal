<?php
use core\Mail;

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Send emails that are currently in spool queue.",
    'params' 		=>	[
    ],
    'access' => [
        'visibility'        => 'private'
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

Mail::flush();

$context->httpResponse()
        ->body('')
        ->send();