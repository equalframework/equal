<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;

[$params, $providers] = eQual::announce([
    'description'	=>	"Sign a user out.",
    'params' 		=>	[
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_TOKEN_HTTPS'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth'],
]);

/**
 * @var equal\php\Context   $context
 * @var equal\auth\AuthenticationManager    $auth
 */
['context' => $context, 'auth' => $auth ] = $providers;

$authenticated_user_id = $auth->authenticatedUserId();

if($authenticated_user_id <= 0) {
    throw new Exception("user_not_authenticated", EQ_ERROR_NOT_ALLOWED);
}

Setting::assert_value('core', 'security', 'impersonation.enabled', false, ['user_id' => $authenticated_user_id]);
Setting::assert_value('core', 'security', 'impersonation.expiry', 0, ['user_id' => $authenticated_user_id]);

Setting::set_value(
    'core', 'security', 'impersonation.enabled',
    false,
    ['user_id' => $authenticated_user_id]
);

Setting::set_value(
    'core', 'security', 'impersonation.expiry',
    time() - 3600,
    ['user_id' => $authenticated_user_id]
);

$context->httpResponse()
        ->cookie('access_token', '', [
            'expires'   => time() - 3600,
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS'),
            'samesite'  => 'Strict'
        ])
        ->status(205)
        ->send();
