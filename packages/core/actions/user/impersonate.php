<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\User;
use core\setting\Setting;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   => "Starts impersonation for the current user.",
    'params'        => [
        'id'   => [
            'description'   => 'Identifier of the user to impersonate.',
            'type'          => 'integer',
            'required'      => true
        ],
        'duration'  => [
            'description'   => 'Impersonation duration in seconds.',
            'type'          => 'integer',
            'default'       => 3600
        ]
    ],
    'access'        => [
        'visibility' => 'protected'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8'
    ],
    'providers'     => ['context', 'auth', 'access']
]);

/**
 * @var equal\php\Context                   $context
 * @var equal\auth\AuthenticationManager    $auth
 * @var equal\access\AccessController       $access
 */
['context' => $context, 'auth' => $auth, 'access' => $access] = $providers;

$authenticated_user_id = $auth->authenticatedUserId();

if($authenticated_user_id <= 0) {
    throw new Exception("user_not_authenticated", EQ_ERROR_NOT_ALLOWED);
}

$target_user_id = (int) $params['id'];

if($target_user_id <= 0) {
    throw new Exception("invalid_user_id", EQ_ERROR_INVALID_PARAM);
}

Setting::assert_value('core', 'security', 'impersonation.allowed', false, ['user_id' => $authenticated_user_id]);
Setting::assert_value('core', 'security', 'impersonation.enabled', false, ['user_id' => $authenticated_user_id]);
Setting::assert_value('core', 'security', 'impersonation.user_id', 0, ['user_id' => $authenticated_user_id]);
Setting::assert_value('core', 'security', 'impersonation.expiry', 0, ['user_id' => $authenticated_user_id]);

if($target_user_id === $authenticated_user_id) {
    Setting::set_value(
        'core', 'security', 'impersonation.enabled',
        false,
        ['user_id' => $authenticated_user_id]
    );
    throw new Exception("impersonation_disabled", 0);
}

$can_impersonate = Setting::get_value(
    'core', 'security', 'impersonation.allowed',
    false,
    ['user_id' => $authenticated_user_id]
);

if(!$can_impersonate) {
    throw new Exception("impersonation_not_allowed", EQ_ERROR_NOT_ALLOWED);
}

Setting::set_value(
    'core', 'security', 'impersonation.enabled',
    true,
    ['user_id' => $authenticated_user_id]
);

// check that target user exists (the target user does not need to be active, validated or confirmed)
$targetUser = User::id($target_user_id)->first();

if(!$targetUser) {
    throw new Exception("target_user_not_found", EQ_ERROR_INVALID_USER);
}

$duration = max(600, (int) $params['duration']);
$expiry = time() + $duration;

Setting::set_value(
    'core', 'security', 'impersonation.user_id',
    $target_user_id,
    ['user_id' => $authenticated_user_id]
);

Setting::set_value(
    'core', 'security', 'impersonation.expiry',
    $expiry,
    ['user_id' => $authenticated_user_id]
);

$context->httpResponse()
        ->status(205)
        ->send();
