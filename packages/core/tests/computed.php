<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
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
        ],

    '1002' => [
            'description'   =>  "Checking related dependents reset from a non-default language.",
            'arrange'       =>  function () {
                    $test = core\test\Test::create(['string_short' => 'lang 0'])->read(['id'])->first();
                    $test1 = core\test\Test1::create(['test_id' => $test['id']])->read(['id', 'test'])->first();
                    return [
                        'test_id'  => $test['id'],
                        'test1_id' => $test1['id'],
                        'before'   => $test1['test']
                    ];
                },
            'act'           =>  function ($fixtures) {
                    $lang = constant('DEFAULT_LANG') === 'fr' ? 'en' : 'fr';
                    core\test\Test::id($fixtures['test_id'])->update(['string_short' => 'lang 1'], $lang);
                    $test1 = core\test\Test1::id($fixtures['test1_id'])->read(['test'])->first();
                    return $fixtures + ['after' => $test1['test']];
                },
            'assert'        =>  function($result) {
                    return ($result['before'] == 'lang 0' && $result['after'] == 'lang 1');
                },
            'rollback'      =>  function($result) {
                    core\test\Test1::id($result['test1_id'])->delete(true);
                    core\test\Test::id($result['test_id'])->delete(true);
                }
        ]

];
