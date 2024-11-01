<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
                    return (isset($issues['invalid_value']) && $issues['invalid_value'] == 'Value is not amongst selection choices.');
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
];