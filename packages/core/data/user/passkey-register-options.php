<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\User;
use equal\auth\JWT;
use lbuchs\WebAuthn\WebAuthn;

[$params, $providers] = announce([
    'description'   => 'Returns the options needed for passkey registration.',
    'params'        => [
        'login' => [
            'type'          => 'string',
            'description'   => 'User login or username.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['AUTH_SECRET_KEY', 'APP_NAME', 'BACKEND_URL'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

/**
 * Methods
 */

$getUserFromLoginParam = function(array $params): array {
    $domain = [];
    if(strpos($params['login'], '@') > 0) {
        // cleanup provided email (as login): we strip heading and trailing spaces and remove recipient tag, if any
        list($username, $email_domain) = explode('@', strtolower(trim($params['login'])));
        $username .= '+';

        $domain[] = ['login', '=', substr($username, 0, strpos($username, '+')).'@'.$email_domain];
    }
    else {
        $domain[] = ['username', '=', $params['login']];
    }

    $user = User::search($domain)
        ->read(['id', 'login', 'username', 'passkey_user_handle'])
        ->first(true);

    if(!$user) {
        throw new Exception('user_not_found', EQ_ERROR_UNKNOWN_OBJECT);
    }

    return $user;
};

/**
 * Action
 */

$user = $getUserFromLoginParam($params);

$rp_id = Setting::get_value('core', 'security', 'passkey_rp_id', parse_url(constant('BACKEND_URL'), PHP_URL_HOST));
$rp_name = Setting::get_value('core', 'security', 'passkey_rp_name', constant('APP_NAME'));
$user_verification = Setting::get_value('core', 'security', 'passkey_user_verification', 'preferred');

$cross_platform_attachment = Setting::get_value('core', 'security', 'passkey_cross_platform', true);
if($cross_platform_attachment === 'all') {
    $cross_platform_attachment = null;
}
elseif($cross_platform_attachment === 'cross-platform') {
    $cross_platform_attachment = true;
}
elseif($cross_platform_attachment === 'platform') {
    $cross_platform_attachment = false;
}

$formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
$allowed_formats = [];
foreach($formats as $format) {
    $is_format_allowed = Setting::get_value('core', 'security', "passkey_format_$format", false);
    if($is_format_allowed) {
        $allowed_formats[] = $format;
    }
}

$webAuthn = new WebAuthn($rp_name, $rp_id, $allowed_formats);

$webAuthn->addRootCertificates(EQ_BASEDIR.'/mds-cert');

$user_handle = $user['passkey_user_handle'] ?? null;
if(is_null($user_handle)) {
    $user_handle = bin2hex(random_bytes(16));

    User::id($user['id'])->update(['passkey_user_handle' => $user_handle]);
}

$register_options = $webAuthn->getCreateArgs(
    $user_handle,
    $user['login'],
    $rp_name,
    20,
    true,
    $user_verification,
    $cross_platform_attachment
);

$data = [
    'user_id'   => $auth->userId(),
    'challenge' => $webAuthn->getChallenge()->getHex()
];

$registration_token = JWT::encode(
    $data,
    constant('AUTH_SECRET_KEY')
);

$register_options->register_token = $registration_token;

$context->httpResponse()
        ->status(200)
        ->body((array) $register_options)
        ->send();