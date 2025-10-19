<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\User;

[$params, $providers] = eQual::announce([
    'description'   => "Generates an access token for a specific user.",
    'params'        => [

        'login' => [
            'type'          => 'string',
            'usage'         => 'email',
            'description'   => "Email address to be used as login for the user.",
            'required'      => true
        ],

        'validity_duration' => [
            'type'          => 'integer',
            'min'           => 0,
            'description'   => "Validity duration of the generated token.",
            'help'          => "Zero means use config value.",
            'default'       => 0
        ],

        'no_expiry' => [
            'type'          => 'boolean',
            'description'   => "If true the generated token will not expire.",
            'default'       => false
        ]

    ],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_ACCESS_TOKEN_VALIDITY'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$user = User::search(['login', '=', $params['login']])
    ->read(['id'])
    ->first();

if(is_null($user)) {
    throw new Exception("unknown_user", EQ_ERROR_UNKNOWN_OBJECT);
}

$validity = 0;
if(!$params['no_expiry']) {
    $validity = constant('AUTH_ACCESS_TOKEN_VALIDITY');
    if($params['duration'] > 0) {
        $validity = $params['duration'];
    }
}

$access_token = $auth->token($user['id'], $validity, ['auth_type'  => 'token', 'auth_level' => 1]);

$context->httpResponse()
        ->status(200)
        ->body($access_token)
        ->send();
