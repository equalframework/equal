<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

$tests = [

    '0101' => [
            'description'       =>  "Retrieve authentication service from eQual::announce",
            'return'            =>  ['object'],
            'assert'            =>  function($auth) {
                    return ($auth instanceof equal\auth\AuthenticationManager);
                },
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    return $providers['equal\auth\AuthenticationManager'];
                }
        ],

    '0102' => [
            'description'       =>  "Get auth provider using a custom registered name.",
            'return'            =>  ['object'],
            'assert'            =>  function($auth) {
                    return ($auth instanceof equal\auth\AuthenticationManager);
                },
            'act'               =>  function (){
                    [$params, $providers] = eQual::announce([
                        'providers' => ['@@testAuth' => 'equal\auth\AuthenticationManager']
                    ]);
                    return $providers['@@testAuth'];
                }
        ],

    '0103' => [
            'description'       =>  'Retrieve a valid access token payload.',
            'return'            =>  ['array'],
            'assert'            =>  function($result) {
                    $payload = $result['payload'];
                    return isset($payload['id'], $payload['exp'])
                        && $payload['id'] === EQ_ROOT_USER_ID
                        && $payload['exp'] > time()
                        && $payload['amr'] === []
                        && $payload['auth'] === []
                        && $result['level'] === 0;
                },
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60);

                    return [
                        'payload'   => $auth->retrieveAccessToken($token),
                        'level'     => $auth->getAuthLevel($token)
                    ];
                }
        ],

    '0104' => [
            'description'       =>  'Do not retrieve an expired access token payload.',
            'return'            =>  ['NULL'],
            'expected'          =>  null,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    return $auth->retrieveAccessToken($auth->token(EQ_ROOT_USER_ID, -60));
                }
        ],

    '0105' => [
            'description'       =>  'Return level zero for a legacy JWT authentication state.',
            'return'            =>  ['integer'],
            'expected'          =>  0,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $token = $auth->encodeToken([
                        'id'    => EQ_ROOT_USER_ID,
                        'amr'   => [[
                            'auth_type'  => 'pwd',
                            'auth_level' => 1
                        ]],
                        'iat'   => $now,
                        'exp'   => $now + 60,
                        'trk'   => false
                    ]);

                    return $auth->getAuthLevel($token);
                }
        ],

    '0106' => [
            'description'       =>  'Preserve detailed authentication state and expose standard AMR methods.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $pwd_auth_method = [
                        'method'  => 'pwd',
                        'level'   => 1,
                        'exp'     => $now + 60
                    ];
                    $otp_auth_method = [
                        'method'  => 'otp',
                        'level'   => 2,
                        'exp'     => $now + 30
                    ];
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, $pwd_auth_method);
                    $pwd_payload = $auth->retrieveAccessToken($token);
                    $token = $auth->addAuthMethod($otp_auth_method, $token);
                    $payload = $auth->retrieveAccessToken($token);

                    return $pwd_payload['amr'] === ['pwd']
                        && $pwd_payload['auth'] === [$pwd_auth_method]
                        && $payload['amr'] === ['pwd', 'otp']
                        && $payload['auth'] === [$pwd_auth_method, $otp_auth_method]
                        && $auth->getAuthLevel($token) === 2;
                }
        ],

    '0107' => [
            'description'       =>  'Degrade to the highest authentication level that has not expired.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'  => 'pwd',
                        'level'   => 1,
                        'exp'     => $now + 60
                    ]);
                    $token = $auth->addAuthMethod([
                        'method'  => 'otp',
                        'level'   => 2,
                        'exp'     => $now - 1
                    ], $token);

                    return $auth->getAuthLevel($token) === 1;
                }
        ],

    '0108' => [
            'description'       =>  'Return level zero when every JWT authentication has expired.',
            'return'            =>  ['integer'],
            'expected'          =>  0,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'  => 'pwd',
                        'level'   => 1,
                        'exp'     => time() - 1
                    ]);

                    return $auth->getAuthLevel($token);
                }
        ],

    '0109' => [
            'description'       =>  'Add a temporary authentication without extending the JWT lifetime.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'  => 'pwd',
                        'level'   => 1,
                        'exp'     => $now + 120
                    ]);
                    $payload = $auth->retrieveAccessToken($token);
                    $updated_token = $auth->addAuthMethod([
                        'method'  => 'otp',
                        'level'   => 2,
                        'exp'     => $now + 30
                    ], $token);
                    $updated_payload = $auth->retrieveAccessToken($updated_token);

                    return $updated_payload['exp'] === $payload['exp']
                        && $updated_payload['amr'] === ['pwd', 'otp']
                        && count($updated_payload['auth']) === 2
                        && $auth->getAuthLevel($updated_token) === 2;
                }
        ],

    '0110' => [
            'description'       =>  'Replace a repeated authentication method even when its level changes.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'  => 'otp',
                        'level'   => 1,
                        'exp'     => time() + 10
                    ]);
                    $expected_expiry = time() + 30;
                    $updated_token = $auth->addAuthMethod([
                        'method'  => 'otp',
                        'level'   => 2,
                        'exp'     => $expected_expiry
                    ], $token);
                    $payload = $auth->retrieveAccessToken($updated_token);

                    return count($payload['auth']) === 1
                        && $payload['auth'][0]['level'] === 2
                        && $payload['auth'][0]['exp'] === $expected_expiry
                        && $payload['amr'] === ['otp'];
                }
        ],

    '0111' => [
            'description'       =>  'Reject an incomplete authentication descriptor.',
            'return'            =>  ['integer'],
            'expected'          =>  EQ_ERROR_INVALID_PARAM,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    try {
                        $auth->token(EQ_ROOT_USER_ID, 60, [
                            'method' => 'pwd',
                            'level'  => 1
                        ]);
                    }
                    catch(Exception $e) {
                        return $e->getCode();
                    }
                    return 0;
                }
        ]
];
