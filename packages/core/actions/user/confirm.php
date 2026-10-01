<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'	=>	"Validate a user subscription. This controller is meant to be requested through a link sent by email.",
    'params' 		=>	[
        'code' => [
            'description'   => 'Code for authenticating user.',
            'type'          => 'string',
            'required'      => true
        ],
        'redirect' => [
            'description'   => 'Relative URL to redirect user to, after successful authentication. A reset token is appended to it.',
            'type'          => 'string',
            'usage'         => 'url',
            'default'       => 'auth/#/reset'
        ]
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_SECRET_KEY', 'DEFAULT_LANG'],
    'access'        => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'text/html',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

// initialize local vars with inputs
['orm' => $orm, 'context' => $context, 'auth' => $auth] = $providers;

$showInvalidLinkPage = static function(?string $language = null) use ($context) {
    $language = $language ?: constant('DEFAULT_LANG');
    $file = "packages/core/i18n/{$language}/user_confirm_invalid.html";

    if(!($html = @file_get_contents($file))) {
        throw new Exception("missing_template", EQ_ERROR_INVALID_CONFIG);
    }

    $context->httpResponse()
            ->status(400)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->body($html)
            ->send();
    exit();
};

$code = strtr(str_replace(' ', '+', $params['code']), '-_', '+/');
$padding_length = (4 - strlen($code) % 4) % 4;
$credentials = base64_decode($code.str_repeat('=', $padding_length), true);

if($credentials === false || strpos($credentials, ':') === false) {
    trigger_error("APP::core_user_confirm.invalid_link.malformed_code", EQ_REPORT_WARNING);
    $showInvalidLinkPage();
}

[$login, $password] = explode(':', $credentials, 2);

if(!strlen($login) || !strlen($password)) {
    trigger_error("APP::core_user_confirm.invalid_link.empty_credentials", EQ_REPORT_WARNING);
    $showInvalidLinkPage();
}

$auth->su();

// received password is plain text and must match the stored hash
$ids = $orm->search('core\User', [['login', '=', $login]]);

if(!count($ids)) {
    trigger_error("APP::core_user_confirm.invalid_link.unknown_login", EQ_REPORT_WARNING);
    $showInvalidLinkPage();
}

$users = $orm->read(User::getType(), $ids, ['id', 'login', 'password', 'language']);
$user = null;
$language = null;

if(is_array($users)) {
    foreach($users as $candidate) {
        $language = $language ?? ($candidate['language'] ?? null);
        $candidate_password = $candidate['password'] ?? '';
        $password_matches = strlen($candidate_password) && password_verify($password, $candidate_password);

        if($password_matches) {
            $user = $candidate;
            break;
        }
    }
}

if(!$user) {
    trigger_error("APP::core_user_confirm.invalid_link.credential_mismatch", EQ_REPORT_WARNING);
    $showInvalidLinkPage($language);
}

// mark user as validated (will update status according to USER_ACCOUNT_VALIDATION)
$orm->update(User::getType(), $user['id'], ['validated' => true]);

$response = $context->httpResponse();

if(strlen($params['redirect'])) {
    // Generate a reset token valid for 15 minutes, same as password recovery links.
    $token = $auth->token($user['id'], 60 * 15);
    $url = rtrim(constant('BACKEND_URL'), '/') . '/' . trim($params['redirect'], '/') . '/' . $token;
    header('Location: ' . $url);
    exit();
}

$context->httpResponse()
        ->status(204)
        ->send();
