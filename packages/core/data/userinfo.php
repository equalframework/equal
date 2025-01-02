<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

list($params, $providers) = eQual::announce([
    'description'   => 'Returns descriptor of current User, based on received access_token',
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'BACKEND_URL'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth']
]);
// use equal\orm\ObjectManager

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
list($context, $auth) = [$providers['context'], $providers['auth']];

// retrieve current User identifier (HTTP headers lookup through Authentication Manager)
$user_id = $auth->userId();

// make sure user is authenticated
if($user_id <= 0) {
    throw new Exception('user_unknown', EQ_ERROR_INVALID_USER);
}

// #memo - user has always READ right on its own object
$user = User::id($user_id)
    ->read([
        'id', 'name', 'login', 'validated', 'language',
        'groups_ids' => ['name', 'display_name']
    ])
    ->adapt('json')
    ->first(true);

// make sure the User object is available
if(!$user) {
    throw new Exception('unexpected_error', EQ_ERROR_INVALID_USER);
}

if(!$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

$user['groups'] = array_values(array_map(function ($a) {return $a['name'];}, $user['groups_ids']));

// renew JWT access token
$access_token  = $auth->renewedToken(constant('AUTH_ACCESS_TOKEN_VALIDITY'));

// send back basic info of the User object
$context->httpResponse()
    ->cookie('access_token',  $access_token, [
        'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        'httponly'  => true,
        'secure'    => constant('AUTH_TOKEN_HTTPS'),
        'domain'    => parse_url(constant('BACKEND_URL'), PHP_URL_HOST)
    ])
    ->body($user)
    ->send();
