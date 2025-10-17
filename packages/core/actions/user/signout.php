<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'	=>	"Sign a user out.",
    'params' 		=>	[
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_TOKEN_HTTPS'],
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
            'secure'    => constant('AUTH_TOKEN_HTTPS')
        ])
        ->status(204)
        ->send();
