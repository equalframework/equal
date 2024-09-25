<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Passkey;
use equal\auth\JWT;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use lbuchs\WebAuthn\WebAuthn;

[$params, $providers] = announce([
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
    'access' => [
        'visibility'    => 'public'
    ],
    'constants'     => ['BACKEND_URL', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$rp_id = 'localhost';
$formats = ['android-key', 'android-safetynet', 'apple', 'fido-u2f', 'none', 'packed', 'tpm'];

$webAuthn = new WebAuthn('eQual Passkey', $rp_id, $formats);

$client_data_json = base64_decode($params['client_data_json']);
$attestation_object = base64_decode($params['attestation_object']);

$register_token = JWT::decode($params['register_token']);

$data = $webAuthn->processCreate($client_data_json, $attestation_object, ByteBuffer::fromHex($register_token['payload']['challenge']), false, true, false);

Passkey::create([
    'user_id'               => $register_token['payload']['user_id'],
    'credential_id'         => bin2hex($data->credentialId),
    'credential_public_key' => $data->credentialPublicKey,
]);

$context->httpResponse()
        ->status(200)
        ->body(['success' => true])
        ->send();
