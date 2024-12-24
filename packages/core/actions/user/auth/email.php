<?php
/*
    This file is part of the Discope property management software.
    Author: Yesbabylon SRL, 2020-2024
    License: GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'	=>	"Attempts to log a user in with a nonce token.",
    'params' 		=>	[
        'nonce' =>  [
            'description'   => "Nonce token to be used for authentication.",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'        => 'public'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'auth', 'orm'],
    'constants'     => ['AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\orm\ObjectManager            $orm
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'orm' => $orm, 'auth' => $auth] = $providers;

$jwt = $params['nonce'];

$check = $auth->verifyToken($jwt, constant('AUTH_SECRET_KEY'));
if($check === false || $check <= 0) {
    throw new Exception('invalid_token', EQ_ERROR_NOT_ALLOWED);
}

$token = $auth->decodeToken($jwt);

if(!isset($token['payload'])) {
    throw new Exception('malformed_token', EQ_ERROR_NOT_ALLOWED);
}

$payload = $token['payload'];

if(!isset($payload['email'])) {
    throw new Exception('malformed_token', EQ_ERROR_NOT_ALLOWED);
}

$user = User::search(['login', '=', $payload['email']])
    ->read(['id', 'validated'])
    ->first(true);

if(!$user || !$user['validated']) {
    throw new Exception("user_not_validated", EQ_ERROR_NOT_ALLOWED);
}

// generate a JWT access token
$access_token = $auth->token(
        // user identifier
        $user['id'],
        // validity of the token
        constant('AUTH_ACCESS_TOKEN_VALIDITY'),
        // authentication method to register to AMR
        [
            'auth_type'  => 'email',
            'auth_level' => 1
        ]
    );

$context->httpResponse()
        ->cookie('access_token',  $access_token, [
            'expires'   => time() + constant('AUTH_ACCESS_TOKEN_VALIDITY'),
            'httponly'  => true,
            'secure'    => constant('AUTH_TOKEN_HTTPS'),
            'domain'    => parse_url(constant('BACKEND_URL'), PHP_URL_HOST)
        ])
        ->status(204)
        ->send();
