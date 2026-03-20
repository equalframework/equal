<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Mail;

// announce script and fetch parameters values
list($params, $providers) = eQual::announce([
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
        ->status(204)
        ->send();
