<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/


$tests = [
    // each key of the associative array is a test identifier that maps to a test descriptor
    '0101' => [
            'description'   =>  "Demo test",
            'return'        => ['integer','boolean'],
            'arrange'       =>  function () {
                    // every action that is necessary to prepare the tests
                },
            'act'           =>  function () {
                    // this method should return a value whose type matches one of the type given by the `return` property.
                    return 1;
                },
            'assert'        =>  function($result) {
                    // advanced test(s) on the result returned by the act function
                    // this method must return a boolean
                    return ($result > 0);
                }
        ]
];