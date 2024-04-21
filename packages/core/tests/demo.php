<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

/**
 * A `$test` var is expected to be set by each tests set.
 * That var is used in the `core_test_package` controller and, since tests sets are loaded using `include($filename)`,
 * the `$tests` var is shared between tests sets and the parent script.
 */

$tests = [
    // Each key of the associative array is a test identifier that maps to a test descriptor.
    '0101' => [
            // short description of the test being performed (max 70 chars).
            'description'   =>  'Demo test',
            'help'          =>  "This is a full description of the reason of the test.\n
                Example: \n
                Given an initial value, \n
                When that value is multplied by a factor 2, \n
                Then the result should be the double of the initial value.
            ",
            /**
             * `arrange` is meant to prepare the environment so that all prerequisites of the test are met.
            */
            'arrange'       =>  function () {
                    // All necessary actions to prepare the scenario.
                    return 1;
                },
            /**
             * `act` is the actual processing that is being tested. It consists of a sequence of instructions and returns a resulting value.
             * @param   mixed   $arrange_result Value returned by the `arrange` callback.
             * @return  mixed   Several values can be retured by using an array.
             */
            'act'           =>  function ($arrange_result) {
                    // The instructions being tested.
                    // This method should always return a value whose type matches one of the type given by the `return` property.
                    return (2 * $arrange_result);
                },
            /**
             * `assert` is meant to verify that the required conditions are fullfilled and that the processing is consistent with the expect result.
             * @param   mixed   $result     Value returned from the `act` callback.
             * @return  bool    The callback is meant to return a boolean value.
             */
            'assert'        =>  function($act_result) {
                    // Advanced test(s) on the result returned by the act function.
                    // This method must always return a boolean which indicates if the test succeeded (true) or failed (false).
                    return ($act_result == 2);
                },
            /**
             * The `rollback` property provides a callback for cleaning (i.e. undo what was don in the arrange callback).
             * @param   mixed   $act_result     Value returned from the `act` callback.
             */
            'rollback'  => function($act_result) {

                }
        ]
];