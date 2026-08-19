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
                    list($params, $providers) = eQual::announce([
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
                    list($params, $providers) = eQual::announce([
                        'providers' => ['@@testAuth' => 'equal\auth\AuthenticationManager']
                    ]);
                    return $providers['@@testAuth'];
                }
        ],

    '0103' => [
            'description'       =>  'Retrieve a valid access token payload.',
            'return'            =>  ['array'],
            'assert'            =>  function($payload) {
                    return isset($payload['id'], $payload['exp'])
                        && $payload['id'] === EQ_ROOT_USER_ID
                        && $payload['exp'] > time();
                },
            'act'               =>  function () {
                    list($params, $providers) = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    return $auth->retrieveAccessToken($auth->token(EQ_ROOT_USER_ID, 60));
                }
        ],

    '0104' => [
            'description'       =>  'Do not retrieve an expired access token payload.',
            'return'            =>  ['NULL'],
            'expected'          =>  null,
            'act'               =>  function () {
                    list($params, $providers) = eQual::announce([
                        'providers' => ['equal\auth\AuthenticationManager']
                    ]);
                    $auth = $providers['equal\auth\AuthenticationManager'];

                    return $auth->retrieveAccessToken($auth->token(EQ_ROOT_USER_ID, -60));
                }
        ]
];
