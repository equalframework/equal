<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Sign a user out.",
    'params' 		=>	[
    ],
    'constants'     => ['AUTH_TOKEN_HTTPS'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ]
]);


$context->httpResponse()
        ->cookie('access_token', '', ['expires' => time(), 'httponly' => true, 'secure' => constant('AUTH_TOKEN_HTTPS')])
        ->cookie('refresh_token', '', ['expires' => time(), 'httponly' => true, 'secure' => constant('AUTH_TOKEN_HTTPS')])
        ->status(204)
        ->send();