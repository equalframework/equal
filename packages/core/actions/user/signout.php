<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

// announce script and fetch parameters values
list($params, $providers) = eQual::announce([
    'description'	=>	"Sign a user out.",
    'params' 		=>	[
    ],
    'constants'     => ['ROOT_APP_URL', 'AUTH_TOKEN_HTTPS'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context'],
]);

/**
 * @var equal\php\Context   $context
 */
['context' => $context ] = $providers;


$context->httpResponse()
        ->cookie('access_token', '', [
            'expires'   => time(),
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS'),
            'domain'    => parse_url(constant('BACKEND_URL'), PHP_URL_HOST)
        ])
        ->status(204)
        ->send();
