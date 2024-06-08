<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\User;

$tests = [

    '1001' => [
            'description'   =>  "Checking `User::getConstraints()` 'username' validation rule with invalid value.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        User::id(1)->update(['username' => '-invalid_name-']);
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
    '1002' => [
            'description'   =>  "Checking `User::getConstraints()` 'username' validation rule with valid value.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        User::id(1)->update(['username' => 'valid-name']);
                    }
                    catch(Exception $e) {
                        $result = $e->getCode();
                    }
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 0);
                },
            'rollback'      => function() {
                    User::id(1)->update(['username' => null]);
                }
        ],
    '1011' => [
            'description'   =>  "Checking usage validation 'text/plain:9' with valid value.",
            'act'           =>  function () {
                    $result = 0;
                    try {
                        core\test\Test::create(['string_short' => '123456789']);
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
                        core\test\Test::create(['string_short' => '0123456789']);
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
                        core\test\Test::create(['float_amount' => 'abc']);
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
                        core\test\Test::create(['float_amount' => '123,456']);
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
                        core\test\Test::create(['float_amount' => 123.456789]);
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
                        core\test\Test::create(['float_amount' => 123.4567]);
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
                        core\test\Test::create(['string_currency' => 'tralala']);
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
                        core\test\Test::create(['string_currency' => 'USD']);
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
                        core\test\Test::create(['datetime' => 'foo']);
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
                        core\test\Test::create(['datetime' => 1711990542]);
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