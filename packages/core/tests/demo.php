<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

/**
 * A global var `$test` is expected to be set by each tests set (that var is used in the `core_package_test` controller).
 * As the current file is injected in the global scope, this line is not mandatory and is left in order to ease the understanding.
 *
 * @var array   $test   Global var holding the test descriptors.
 */
global $test;

$tests = [
    // each key of the associative array is a test identifier that maps to a test descriptor
    '0101' => [
            'description'   =>  "Demo test",
            'return'        => ['integer','boolean'],
            'arrange'       =>  function () {
                    // All necessary actions to prepare the scenario.
                },
            'act'           =>  function () {
                    // The instructions being tested.
                    // This method should always return a value whose type matches one of the type given by the `return` property.
                    return 1;
                },
            /**
             * `assert` receives the value returned by the callback of the `act` property.
             * @return bool     The callback is meant to return a boolean value.
             */
            'assert'        =>  function($result) {
                    // Advanced test(s) on the result returned by the act function.
                    // This method must always return a boolean which indicates if the test succeeded (true) or failed (false).
                    return ($result > 0);
                },
            /**
             * The `rollback` property provides a callback for cleaning (i.e. undo what was don in the arrange callback).
             */
            'rollback'  => function() {

                }
        ]
];