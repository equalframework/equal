<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

$tests = [

    '1001' => [
            'description'   =>  "Checking dependents chaining with computed field of related object.",
            'act'           =>  function () {
                    $result = [];
                    $test = core\test\Test::create(['string_short' => 'test 0'])->read(['id'])->first();
                    $test1 = core\test\Test1::create(['test_id' => $test['id']])->read(['id', 'test'])->first();
                    $result[] = $test1['test'];
                    core\test\Test::id($test['id'])->update(['string_short' => 'test 1']);
                    $test1 = core\test\Test1::id($test1['id'])->read(['test'])->first();
                    $result[] = $test1['test'];
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result[0] == 'test 0' && $result[1] == 'test 1');
                }
        ]

];