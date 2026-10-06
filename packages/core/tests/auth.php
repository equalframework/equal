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
                        && $payload['acr'] === 'urn:equal:auth:level:0'
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
                        'exp'     => $now + 60
                    ];
                    $totp_auth_method = [
                        'method'  => 'totp',
                        'exp'     => $now + 30
                    ];
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, $pwd_auth_method);
                    $pwd_payload = $auth->retrieveAccessToken($token);
                    $token = $auth->addAuthMethod($totp_auth_method, $token);
                    $payload = $auth->retrieveAccessToken($token);

                    return $pwd_payload['amr'] === ['pwd']
                        && $pwd_payload['auth'] === [$pwd_auth_method]
                        && $pwd_payload['acr'] === 'urn:equal:auth:level:1'
                        && $payload['amr'] === ['pwd', 'otp']
                        && $payload['auth'] === [$pwd_auth_method, $totp_auth_method]
                        && $payload['acr'] === 'urn:equal:auth:level:2'
                        && $auth->getAuthLevel($token) === 2;
                }
        ],

    '0107' => [
            'description'       =>  'Fall back to level one when the second proof has expired.',
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
                        'exp'     => $now + 60
                    ]);
                    $token = $auth->addAuthMethod([
                        'method'  => 'totp',
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
                        'exp'     => $now + 120
                    ]);
                    $payload = $auth->retrieveAccessToken($token);
                    $updated_token = $auth->addAuthMethod([
                        'method'  => 'totp',
                        'exp'     => $now + 30
                    ], $token);
                    $updated_payload = $auth->retrieveAccessToken($updated_token);

                    return $updated_payload['exp'] === $payload['exp']
                        && $updated_payload['amr'] === ['pwd', 'otp']
                        && count($updated_payload['auth']) === 2
                        && $updated_payload['acr'] === 'urn:equal:auth:level:2'
                        && $auth->getAuthLevel($updated_token) === 2;
                }
        ],

    '0110' => [
            'description'       =>  'Replace a repeated authentication proof.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'act'               =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'  => 'totp',
                        'exp'     => time() + 10
                    ]);
                    $expected_expiry = time() + 30;
                    $updated_token = $auth->addAuthMethod([
                        'method'  => 'totp',
                        'exp'     => $expected_expiry
                    ], $token);
                    $payload = $auth->retrieveAccessToken($updated_token);

                    return count($payload['auth']) === 1
                        && $payload['auth'][0]['exp'] === $expected_expiry
                        && $payload['amr'] === ['otp']
                        && $payload['acr'] === 'urn:equal:auth:level:1'
                        && $auth->getAuthLevel($updated_token) === 1;
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
                            'method' => 'pwd'
                        ]);
                    }
                    catch(Exception $e) {
                        return $e->getCode();
                    }
                    return 0;
                }
        ],

    '0112' => [
            'description'       =>  'Keep TOTP and email OTP distinct in auth while projecting both to OTP in AMR.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $totp_auth_method = [
                        'method'  => 'totp',
                        'exp'     => $now + 30
                    ];
                    $email_otp_auth_method = [
                        'method'  => 'email_otp',
                        'exp'     => $now + 30
                    ];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60, $totp_auth_method);
                    $token = $auth->addAuthMethod($email_otp_auth_method, $token);
                    $payload = $auth->retrieveAccessToken($token);

                    return $payload['amr'] === ['otp']
                        && $payload['auth'] === [$totp_auth_method, $email_otp_auth_method]
                        && $payload['acr'] === 'urn:equal:auth:level:1'
                        && $auth->getAuthLevel($token) === 1;
                }
        ],

    '0113' => [
            'description'       =>  'Store email OTP challenges outside the authentication factor hierarchy.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    return is_subclass_of(
                            core\security\challenge\EmailOtpChallenge::class,
                            equal\orm\Model::class
                        )
                        && !is_subclass_of(
                            core\security\challenge\EmailOtpChallenge::class,
                            core\security\AuthenticationFactor::class
                        )
                        && core\security\challenge\EmailOtpChallenge::getModelTable()
                            !== core\security\AuthenticationFactor::getModelTable();
                }
        ],

    '0114' => [
            'description'       =>  'Consume a pending email OTP challenge.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    $challenge_id = 0;

                    try {
                        $challenge = core\security\challenge\EmailOtpChallenge::create([
                                'user_id'       => EQ_ROOT_USER_ID,
                                'code_hash'     => password_hash('123456', PASSWORD_BCRYPT),
                                'expires_at'    => time() + 60
                            ])
                            ->first();

                        $challenge_id = $challenge['id'];

                        core\security\challenge\EmailOtpChallenge::id($challenge_id)
                            ->transition('consume');

                        $challenge = core\security\challenge\EmailOtpChallenge::id($challenge_id)
                            ->read(['status', 'consumed_at'])
                            ->first();

                        return $challenge['status'] === 'consumed'
                            && $challenge['consumed_at'] > 0;
                    }
                    finally {
                        if($challenge_id > 0) {
                            core\security\challenge\EmailOtpChallenge::id($challenge_id)
                                ->delete(true);
                        }
                    }
                }
        ],

    '0115' => [
            'description'       =>  'Grant level two to a passkey proof.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $exp = time() + 60;
                    $auth_method = [
                        'method'    => 'passkey',
                        'exp'       => $exp
                    ];
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, $auth_method);
                    $payload = $auth->retrieveAccessToken($token);

                    return $auth->getAuthLevel($token) === 2
                        && $payload['acr'] === 'urn:equal:auth:level:2'
                        && $payload['auth'] === [$auth_method];
                }
        ],

    '0116' => [
            'description'       =>  'Read a legacy auth descriptor and normalize it when adding a proof.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $legacy_token = $auth->encodeToken([
                        'id'    => EQ_ROOT_USER_ID,
                        'sub'   => EQ_ROOT_USER_ID,
                        'amr'   => ['pwd'],
                        'auth'  => [[
                            'method'    => 'pwd',
                            'level'     => 1,
                            'exp'       => $now + 60
                        ]],
                        'iat'   => $now,
                        'exp'   => $now + 60,
                        'trk'   => false
                    ]);

                    $legacy_level = $auth->getAuthLevel($legacy_token);
                    $updated_token = $auth->addAuthMethod([
                        'method'    => 'totp',
                        'exp'       => $now + 60
                    ], $legacy_token);
                    $payload = $auth->retrieveAccessToken($updated_token);

                    return $legacy_level === 1
                        && $payload['auth'] === [
                            ['method' => 'pwd', 'exp' => $now + 60],
                            ['method' => 'totp', 'exp' => $now + 60]
                        ]
                        && $payload['acr'] === 'urn:equal:auth:level:2'
                        && $auth->getAuthLevel($updated_token) === 2;
                }
        ],

    '0117' => [
            'description'       =>  'Combine password and email OTP proofs at level two.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $exp = time() + 60;
                    $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                        'method'    => 'pwd',
                        'exp'       => $exp
                    ]);
                    $token = $auth->addAuthMethod([
                        'method'    => 'email_otp',
                        'exp'       => $exp
                    ], $token);
                    $payload = $auth->retrieveAccessToken($token);

                    return $payload['amr'] === ['pwd', 'otp']
                        && $payload['acr'] === 'urn:equal:auth:level:2'
                        && $auth->getAuthLevel($token) === 2;
                }
        ],

    '0118' => [
            'description'       =>  'Issue a new access token while preserving previous authentication proofs.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['context', 'equal\auth\AuthenticationManager']
                    ]);
                    $context = $providers['context'];
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $request = $context->httpRequest();
                    $original_access_token = $request->cookie('access_token');
                    $request->cookie('access_token', '');

                    try {
                        $exp = time() + 60;
                        $pwd_proof = [
                            'method'    => 'pwd',
                            'exp'       => $exp
                        ];
                        $email_otp_proof = [
                            'method'    => 'email_otp',
                            'exp'       => $exp
                        ];

                        $token = $auth->issueAccessToken(
                            EQ_ROOT_USER_ID,
                            $email_otp_proof,
                            [$pwd_proof]
                        );
                        $payload = $auth->retrieveAccessToken($token);

                        return $payload['auth'] === [$pwd_proof, $email_otp_proof]
                            && $payload['amr'] === ['pwd', 'otp']
                            && $payload['acr'] === 'urn:equal:auth:level:2'
                            && $auth->getAuthLevel($token) === 2;
                    }
                    finally {
                        $request->cookie('access_token', (string) $original_access_token);
                    }
                }
        ],

    '0119' => [
            'description'       =>  'Update only a same-user access token without extending its lifetime.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['context', 'equal\auth\AuthenticationManager']
                    ]);
                    $context = $providers['context'];
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $request = $context->httpRequest();
                    $original_access_token = $request->cookie('access_token');

                    try {
                        $now = time();
                        $token = $auth->token(EQ_ROOT_USER_ID, 60, [
                            'method'    => 'pwd',
                            'exp'       => $now + 60
                        ]);
                        $payload = $auth->retrieveAccessToken($token);
                        $request->cookie('access_token', $token);

                        $updated_token = $auth->issueAccessToken(EQ_ROOT_USER_ID, [
                            'method'    => 'totp',
                            'exp'       => $now + 30
                        ]);
                        $updated_payload = $auth->retrieveAccessToken($updated_token);

                        $mismatch_rejected = false;
                        try {
                            $auth->issueAccessToken(EQ_ROOT_USER_ID + 1, [
                                'method'    => 'totp',
                                'exp'       => $now + 30
                            ]);
                        }
                        catch(Exception $e) {
                            $mismatch_rejected = $e->getMessage() === 'authenticated_user_mismatch'
                                && $e->getCode() === EQ_ERROR_NOT_ALLOWED;
                        }

                        return $updated_payload['exp'] === $payload['exp']
                            && $updated_payload['amr'] === ['pwd', 'otp']
                            && $updated_payload['acr'] === 'urn:equal:auth:level:2'
                            && $mismatch_rejected;
                    }
                    finally {
                        $request->cookie('access_token', (string) $original_access_token);
                    }
                }
        ],

    '0120' => [
            'description'       =>  'Issue several authentication methods at once without altering legacy signed tokens.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $auth_methods = [
                        [
                            'method'    => 'pwd',
                            'exp'       => $now + 60
                        ],
                        [
                            'method'    => 'totp',
                            'exp'       => $now + 60
                        ]
                    ];

                    $token = $auth->token(EQ_ROOT_USER_ID, 60, $auth_methods);
                    $payload = $auth->retrieveAccessToken($token);

                    $legacy_payload = [
                        'id'    => EQ_ROOT_USER_ID,
                        'sub'   => EQ_ROOT_USER_ID,
                        'amr'   => ['pwd'],
                        'auth'  => [[
                            'method'    => 'pwd',
                            'level'     => 1,
                            'exp'       => $now + 60
                        ]],
                        'iat'   => $now,
                        'exp'   => $now + 60,
                        'trk'   => false
                    ];
                    $legacy_token = $auth->encodeToken($legacy_payload);

                    $legacy_level = $auth->getAuthLevel($legacy_token);
                    $unchanged_legacy_payload = $auth->retrieveAccessToken($legacy_token);

                    return $payload['auth'] === $auth_methods
                        && $payload['amr'] === ['pwd', 'otp']
                        && $payload['acr'] === 'urn:equal:auth:level:2'
                        && $auth->getAuthLevel($token) === 2
                        && $auth->verifyToken($legacy_token, constant('AUTH_SECRET_KEY'))
                        && $legacy_level === 1
                        && $unchanged_legacy_payload === $legacy_payload;
                }
        ],

    '0121' => [
            'description'       =>  'Preserve explicit authentication claims when renewing a token.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['context', 'equal\auth\AuthenticationManager']
                    ]);
                    $context = $providers['context'];
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $request = $context->httpRequest();
                    $original_access_token = $request->cookie('access_token');

                    try {
                        $exp = time() + 60;
                        $auth_methods = [
                            ['method' => 'pwd', 'exp' => $exp],
                            ['method' => 'totp', 'exp' => $exp]
                        ];
                        $token = $auth->token(EQ_ROOT_USER_ID, 60, $auth_methods);
                        $request->cookie('access_token', $token);

                        $renewed_token = $auth->renewedToken(120);
                        $payload = $auth->retrieveAccessToken($renewed_token);

                        return $payload['auth'] === $auth_methods
                            && $payload['amr'] === ['pwd', 'otp']
                            && $payload['acr'] === 'urn:equal:auth:level:2';
                    }
                    finally {
                        $request->cookie('access_token', (string) $original_access_token);
                    }
                }
        ],

    '0122' => [
            'description'       =>  'Issue and verify MFA challenges only for their intended flow.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $now = time();
                    $password_proof = [
                        'method'    => 'pwd',
                        'exp'       => $now + 60
                    ];

                    $token = $auth->issueMfaChallengeToken(
                        EQ_ROOT_USER_ID,
                        'totp',
                        30,
                        [$password_proof]
                    );
                    $payload = $auth->verifyMfaChallengeToken($token, 'totp');

                    $wrong_flow_rejected = false;
                    try {
                        $auth->verifyMfaChallengeToken($token, 'email_otp');
                    }
                    catch(Exception $exception) {
                        $wrong_flow_rejected = $exception->getMessage() === 'invalid_token'
                            && $exception->getCode() === EQ_ERROR_INVALID_PARAM;
                    }

                    $parts = explode('.', $token);
                    $parts[2][0] = $parts[2][0] === 'A' ? 'B' : 'A';
                    $tampered_token = implode('.', $parts);

                    $tampered_token_rejected = false;
                    try {
                        $auth->verifyMfaChallengeToken($tampered_token, 'totp');
                    }
                    catch(Exception $exception) {
                        $tampered_token_rejected = $exception->getMessage() === 'invalid_token'
                            && $exception->getCode() === EQ_ERROR_NOT_ALLOWED;
                    }

                    return !isset($payload['id'])
                        && $payload['type'] === 'mfa_challenge'
                        && $payload['mfa_method'] === 'totp'
                        && $payload['sub'] === EQ_ROOT_USER_ID
                        && $payload['exp'] === $payload['iat'] + 30
                        && $payload['auth'] === [$password_proof]
                        && $payload['amr'] === ['pwd']
                        && $wrong_flow_rejected
                        && $tampered_token_rejected;
                }
        ],

    '0123' => [
            'description'       =>  'Use email OTP when TOTP is enabled but neither required nor configured.',
            'return'            =>  ['array'],
            'arrange'           =>  function () {
                    $login = 'auth_mfa_email_fallback_test@example.com';
                    core\User::search(['login', '=', $login])->delete(true);

                    $user = core\User::create([
                            'login'         => $login,
                            'password'      => 'auth-mfa-email-fallback-test',
                            'validated'     => true,
                            'allow_auth'    => true
                        ])
                        ->first();

                    core\setting\Setting::assert_value('core', 'security', 'auth.totp.enabled', true);
                    core\setting\Setting::assert_value('core', 'security', 'auth.password.totp_required', false);
                    core\setting\Setting::assert_value('core', 'security', 'auth.password.email_otp_required', true);

                    $selector = ['user_id' => $user['id']];
                    core\setting\Setting::set_value('core', 'security', 'auth.totp.enabled', true, $selector);
                    core\setting\Setting::set_value('core', 'security', 'auth.password.totp_required', false, $selector);
                    core\setting\Setting::set_value('core', 'security', 'auth.password.email_otp_required', true, $selector);

                    return $user['id'];
                },
            'act'               =>  function ($user_id) {
                    ['auth' => $auth] = eQual::inject(['auth']);

                    try {
                        $auth->su($user_id);
                        $method = $auth->getUserMfa();
                    }
                    catch(Throwable $exception) {
                        $method = $exception->getMessage();
                    }
                    finally {
                        $auth->su();
                    }

                    return [
                        'user_id'   => $user_id,
                        'method'    => $method
                    ];
                },
            'assert'            =>  function ($result) {
                    return $result['method'] === 'email_otp';
                },
            'rollback'          =>  function ($result) {
                    core\setting\SettingValue::search(['user_id', '=', $result['user_id']])->delete(true);
                    core\User::id($result['user_id'])->delete(true);
                }
        ],

    '0124' => [
            'description'       =>  'Preserve the password proof after TOTP enrollment.',
            'return'            =>  ['boolean'],
            'expected'          =>  true,
            'test'              =>  function () {
                    [$params, $providers] = eQual::announce([
                        'providers' => ['context', 'equal\auth\AuthenticationManager']
                    ]);
                    $context = $providers['context'];
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    $request = $context->httpRequest();
                    $original_access_token = $request->cookie('access_token');
                    $request->cookie('access_token', '');

                    try {
                        $now = time();
                        $password_proof = [
                            'method'    => 'pwd',
                            'exp'       => $now + 60
                        ];
                        $challenge_token = $auth->issueMfaChallengeToken(
                            EQ_ROOT_USER_ID,
                            'totp',
                            30,
                            [$password_proof]
                        );
                        $challenge = $auth->verifyMfaChallengeToken($challenge_token, 'totp');
                        $totp_proof = [
                            'method'    => 'totp',
                            'exp'       => $now + 60
                        ];

                        $token = $auth->issueAccessToken(
                            $challenge['sub'],
                            $totp_proof,
                            $challenge['auth']
                        );
                        $payload = $auth->retrieveAccessToken($token);

                        return $payload['auth'] === [$password_proof, $totp_proof]
                            && $payload['amr'] === ['pwd', 'otp']
                            && $payload['acr'] === 'urn:equal:auth:level:2'
                            && $auth->getAuthLevel($token) === 2;
                    }
                    finally {
                        $request->cookie('access_token', (string) $original_access_token);
                    }
                }
        ],

    '0125' => [
            'description'       =>  'Expose canonical TOTP and email OTP discovery data.',
            'return'            =>  ['array'],
            'arrange'           =>  function () {
                    $login = 'auth_discovery_contract_test@example.com';
                    core\User::search(['login', '=', $login])->delete(true);

                    $user = core\User::create([
                            'login'         => $login,
                            'password'      => 'auth-discovery-contract-test',
                            'validated'     => true,
                            'allow_auth'    => true
                        ])
                        ->first();

                    core\setting\Setting::assert_value('core', 'security', 'auth.totp.enabled', true);
                    core\setting\Setting::assert_value('core', 'security', 'auth.password.totp_required', false);
                    core\setting\Setting::assert_value('core', 'security', 'auth.password.email_otp_required', false);
                    core\setting\Setting::assert_value('core', 'security', 'auth.email_otp.digits', 6);

                    $selector = ['user_id' => $user['id']];
                    core\setting\Setting::set_value('core', 'security', 'auth.totp.enabled', true, $selector);
                    core\setting\Setting::set_value('core', 'security', 'auth.password.totp_required', false, $selector);
                    core\setting\Setting::set_value('core', 'security', 'auth.password.email_otp_required', true, $selector);

                    core\security\factor\TotpKey::create([
                        'user_id'  => $user['id'],
                        'status'   => 'active',
                        'digits'   => 8
                    ]);

                    return [
                        'user_id'   => $user['id'],
                        'login'     => $login
                    ];
                },
            'act'               =>  function ($fixture) {
                    $fixture['result'] = eQual::run('get', 'core_signin-info', [
                        'login' => $fixture['login']
                    ]);

                    return $fixture;
                },
            'assert'            =>  function ($fixture) {
                    $methods_data = $fixture['result']['methods_data'] ?? [];

                    return ($methods_data['pwd']['mfa_required'] ?? false) === true
                        && ($methods_data['totp']['enabled'] ?? false) === true
                        && ($methods_data['totp']['digits'] ?? null) === 8
                        && ($methods_data['email_otp']['required'] ?? false) === true
                        && ($methods_data['email_otp']['digits'] ?? null) === 6
                        && ($methods_data['otp']['digits'] ?? null) === 8;
                },
            'rollback'          =>  function ($fixture) {
                    core\security\factor\TotpKey::search(['user_id', '=', $fixture['user_id']])->delete(true);
                    core\setting\SettingValue::search(['user_id', '=', $fixture['user_id']])->delete(true);
                    core\User::id($fixture['user_id'])->delete(true);
                }
        ],

    '0126' => [
            'description'       =>  'Use the TOTP challenge timeout independently from the email OTP timeout.',
            'return'            =>  ['array'],
            'arrange'           =>  function () {
                    $login = 'auth_totp_timeout_test@example.com';
                    core\User::search(['login', '=', $login])->delete(true);

                    $user = core\User::create([
                            'login'         => $login,
                            'password'      => 'auth-totp-timeout-test',
                            'validated'     => true,
                            'allow_auth'    => true
                        ])
                        ->first();

                    core\setting\Setting::assert_value('core', 'security', 'auth.totp.enabled', true);
                    core\setting\Setting::assert_value('core', 'security', 'auth.password.totp_required', false);
                    core\setting\Setting::assert_value('core', 'security', 'auth.totp.timeout', 300);
                    core\setting\Setting::assert_value('core', 'security', 'auth.email_otp.timeout', 300);

                    $totp_timeout = core\setting\Setting::get_value('core', 'security', 'auth.totp.timeout', 300);
                    $email_otp_timeout = core\setting\Setting::get_value('core', 'security', 'auth.email_otp.timeout', 300);

                    core\setting\Setting::set_value('core', 'security', 'auth.totp.timeout', 37);
                    core\setting\Setting::set_value('core', 'security', 'auth.email_otp.timeout', 113);

                    $selector = ['user_id' => $user['id']];
                    core\setting\Setting::set_value('core', 'security', 'auth.totp.enabled', true, $selector);
                    core\setting\Setting::set_value('core', 'security', 'auth.password.totp_required', true, $selector);

                    core\security\factor\TotpKey::create([
                        'user_id'  => $user['id'],
                        'status'   => 'active'
                    ]);

                    return [
                        'user_id'               => $user['id'],
                        'login'                 => $login,
                        'original_totp_timeout' => $totp_timeout,
                        'original_email_timeout' => $email_otp_timeout
                    ];
                },
            'act'               =>  function ($fixture) {
                    ['auth' => $auth] = eQual::inject(['auth']);

                    try {
                        $auth->su(0);
                        $response = eQual::run('do', 'core_user_auth_pwd', [
                            'login'     => $fixture['login'],
                            'password'  => 'auth-totp-timeout-test'
                        ]);
                        $challenge = $auth->verifyMfaChallengeToken($response['auth_token'], 'totp');

                        $fixture['challenge_method'] = $response['challenge']['method'] ?? null;
                        $fixture['challenge_validity'] = $challenge['exp'] - $challenge['iat'];
                    }
                    finally {
                        $auth->su();
                    }

                    return $fixture;
                },
            'assert'            =>  function ($fixture) {
                    return $fixture['challenge_method'] === 'totp'
                        && $fixture['challenge_validity'] === 37;
                },
            'rollback'          =>  function ($fixture) {
                    if(!is_array($fixture)) {
                        return;
                    }

                    core\setting\Setting::set_value(
                        'core',
                        'security',
                        'auth.totp.timeout',
                        $fixture['original_totp_timeout']
                    );
                    core\setting\Setting::set_value(
                        'core',
                        'security',
                        'auth.email_otp.timeout',
                        $fixture['original_email_timeout']
                    );
                    core\security\factor\TotpKey::search(['user_id', '=', $fixture['user_id']])->delete(true);
                    core\setting\SettingValue::search(['user_id', '=', $fixture['user_id']])->delete(true);
                    core\User::id($fixture['user_id'])->delete(true);
                }
        ]
];
