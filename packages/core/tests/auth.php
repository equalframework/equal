<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
        ]
];