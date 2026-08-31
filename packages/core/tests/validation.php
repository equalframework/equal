<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
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
        ]
];