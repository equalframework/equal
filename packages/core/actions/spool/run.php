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
    'providers'     => ['context', 'orm', 'auth']
]);


// initialize local vars with inputs
list($om, $context, $auth) = [ $providers['orm'], $providers['context'], $providers['auth'] ];

Mail::flush();

$context->httpResponse()
        ->body('')
        ->send();