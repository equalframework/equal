<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\test\Test;

$tests = [

    '1011' => [
            'description'   =>  "Checking usage validation 'text/plain:9' with valid value.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['string_short' => '123456789']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 0);
                }
        ],
    '1012' => [
            'description'   =>  "Checking usage validation 'text/plain:9' with invalid (size overflow).",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['string_short' => '0123456789']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1021' => [
            'description'   =>  "Checking usage validation 'amount/money' with invalid (not a number).",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['float_amount' => 'abc']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1022' => [
            'description'   =>  "Checking usage validation 'amount/money' with invalid (string).",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['float_amount' => '123,456']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1023' => [
            'description'   =>  "Checking usage validation 'amount/money' with invalid (decimal digits overflow).",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['float_amount' => 123.456789]);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1024' => [
            'description'   =>  "Checking usage validation 'amount/money' with valid.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['float_amount' => 123.4567]);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 0);
                }
        ],
    '1031' => [
            'description'   =>  "Checking usage validation 'currency' with invalid.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['string_currency' => 'tralala']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1032' => [
            'description'   =>  "Checking usage validation 'currency' with valid.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['string_currency' => 'USD']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 0);
                }
        ],
    '1041' => [
            'description'   =>  "Checking usage validation 'datetime' with invalid (string).",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['datetime' => 'foo']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == EQ_ERROR_INVALID_PARAM);
                }
        ],
    '1042' => [
            'description'   =>  "Checking usage validation 'datetime' with valid.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        Test::create(['datetime' => 1711990542]);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 0);
                }
        ]
];
