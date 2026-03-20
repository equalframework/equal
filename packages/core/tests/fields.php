<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\Field;

$providers = eQual::inject(['context', 'orm', 'auth', 'access', 'validate']);

$tests = [
    '0101' => [
            'description'       =>  "",
            'act'               =>  function () {
                    $field = new Field([
                            'type'          => 'string',
                            'selection'     => ['a', 'b', 'c']
                        ],
                        'test1');
                    return $field;
                },
            'assert'            =>  function($field) use($providers) {
                    $issues = $providers['validate']->checkConstraints($field, 'd');
                    return (isset($issues['invalid_choice']) && $issues['invalid_choice'] == 'Value is not amongst selection choices.');
                }
        ],
    '0102' => [
            'description'       =>  "",
            'act'               =>  function () {
                    $field = new Field([
                            'type'          => 'string',
                            'selection'     => ['a', 'b', 'c']
                        ],
                        'test2');
                    return $field;
                },
            'assert'            =>  function($field) use($providers) {
                    $issues = $providers['validate']->checkConstraints($field, 'b');
                    return empty($issues);
                }
        ],
    '0103' => [
            'description'       =>  "",
            'act'               =>  function () {
                    $field = new Field([
                        'type'          => 'integer'
                        ],
                        'test3');
                    return $field;
                },
            'assert'            =>  function($field) use($providers) {
                    $issues = $providers['validate']->checkConstraints($field, 10.5);
                    return !(empty($issues));
                }
        ],
    '0104' => [
            'description'       =>  "Testing valid value for selection map on integer.",
            'act'               =>  function () {
                    $field = new Field([
                            'type'          => 'integer',
                            'selection'     => [ 1 => 'a', 2 => 'b', 3 => 'c']
                        ],
                        'test4');
                    return $field;
                },
            'assert'            =>  function($field) use($providers) {
                    $issues = $providers['validate']->checkConstraints($field, 3);
                    return empty($issues);
                }
        ],
    '0105' => [
            'description'       =>  "Testing valid value on selection map on string.",
            'act'               =>  function () {
                    $field = new Field([
                            'type'          => 'string',
                            'selection'     => ['a' => 'Hay', 'b' => 'Bee', 'c' => 'See']
                        ],
                        'test2');
                    return $field;
                },
            'assert'            =>  function($field) use($providers) {
                    $issues = $providers['validate']->checkConstraints($field, 'b');
                    return empty($issues);
                }
        ],
];