<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Passkey;
use core\setting\Setting;
use equal\auth\JWT;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\CBOR\CborDecoder;
use lbuchs\WebAuthn\WebAuthn;

[$params, $providers] = eQual::announce([
    'description'   => 'Register a new passkey for a user.',
    'params'        => [
        'register_token' => [
            'type'          => 'string',
            'description'   => 'Register token that contains the challenge and user information.',
            'required'      => true
        ],
        'transports' => [
            'type'          => 'array',
            'description'   => 'List of the different transports which may be used by the authenticator.',
            'required'      => true
        ],
        'client_data_json' => [
            'type'          => 'string',
            'description'   => 'Client data given to create passkey, is encoded in base64.',
            'required'      => true
        ],
        'attestation_object' => [
            'type'          => 'string',
            'description'   => 'Attestation object containing the new public key, is encoded in base64.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS', 'APP_NAME', 'BACKEND_URL', 'AUTH_SECRET_KEY'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$rp_id = Setting::get_value('core', 'security', 'passkey_rp_id', parse_url(constant('BACKEND_URL'), PHP_URL_HOST));
$rp_name = Setting::get_value('core', 'security', 'passkey_rp_name', constant('APP_NAME'));
$user_verification = Setting::get_value('core', 'security', 'passkey_user_verification', 'preferred');

$passkey_formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];
$allowed_formats = [];
foreach($passkey_formats as $format) {
    $is_format_allowed = Setting::get_value('core', 'security', "passkey_format_$format", true);
    if($is_format_allowed) {
        $allowed_formats[] = $format;
    }
}

// #memo - creating WbAuthn with an empty formats array makes the process fail silently
$webAuthn = new WebAuthn($rp_name, $rp_id, $allowed_formats);

$client_data = base64_decode($params['client_data_json']);
$attestation_object = base64_decode($params['attestation_object']);

// Check that the token has been emitted by this server
if(!$auth->verifyToken($params['register_token'], constant('AUTH_SECRET_KEY'))) {
    throw new Exception("invalid_token", EQ_ERROR_INVALID_PARAM);
}

$register_token = JWT::decode($params['register_token']);

$data = $webAuthn->processCreate(
    $client_data,
    $attestation_object,
    ByteBuffer::fromHex($register_token['payload']['challenge']),
    $user_verification === 'required'
);

// retrieve $attestation (we need fmt)
$attestation = CborDecoder::decode($attestation_object);

Passkey::create([
    'user_id'               => $register_token['payload']['user_id'],
    'credential_id'         => bin2hex($data->credentialId),
    'credential_public_key' => $data->credentialPublicKey,
    'fmt'                   => $attestation['fmt']
]);

$context->httpResponse()
        ->status(204)
        ->send();
