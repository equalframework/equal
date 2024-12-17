<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\setting\Setting;
use core\User;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns user sign in information.',
    'params'        => [
        'login' =>	[
            'description'   => 'User username or login (email).',
            'type'          => 'string',
            'required'      => true
        ],
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'public'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context']
]);

['context' => $context] = $providers;

/**
 * Methods
 */

/**
 * Cleanups login email: strip spaces, remove recipient tag
 *
 * @param string $email
 * @return string
 */
$cleanUpEmail = function(string $email): string {
    $cleaned_email = strtolower(trim($email));
    if(strpos($cleaned_email, '+') !== false) {
        $cleaned_email = preg_replace('/\+.*@/', '@', $cleaned_email);
    }

    return $cleaned_email;
};

/**
 * Action
 */

// "memo - $params['login'] is either username or login (email address)
$user_domain = ['username', '=', $params['login']];

if(filter_var($params['login'], FILTER_VALIDATE_EMAIL)) {
    $user_domain = ['login', '=', $cleanUpEmail($params['login'])];
}

$user = User::search($user_domain)
    ->read(['id', 'passkeys_ids', 'username', 'login'])
    ->first(true);

if(is_null($user)) {
    throw new Exception("user_not_found", EQ_ERROR_INVALID_USER);
}

$global_passkey_creation = Setting::get_value('core', 'security', 'passkey_creation');

$passkey_creation = Setting::get_value(
    'core',
    'auth',
    'passkey_creation',
    $global_passkey_creation,
    ['user_id' => $user['id']]
);

$context->httpResponse()
        ->body([
            'user_handle'               => $user['id'],
            'username'                  => trim($params['login']),
            'has_passkey'               => count($user['passkeys_ids']) > 0,
            'passkey_creation'          => (bool) intval($passkey_creation)
        ])
        ->status(200)
        ->send();
