<?php
/**
*    This file is part of the Qinoa project <http://www.cedricfrancoys.be/qinoa>
*
*    Copyright (C) Cedric Francoys, 2015, Yegen
*    Some Rights Reserved, GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\orm\ObjectManager;


use core\User;
use core\Group;o

list($params, $providers) = eQual::announce([
    'description'   => 'Test unit for Qinoa and Core packages. This unit assumes that a DB connection is available and config vars are set accordingly.',
    'params'        => [
        'package'   => [
            'description'   =>  "Name of the package on which to perform test units",
            'type'          =>  'string',
            'default'       =>  '*'
        ]
    ],
    'providers'     => ['context', 'orm', 'auth', 'access']
]);


// todo : each package should have a test folder with its related test unit in it
// a controller should be dedicated to providing a test-unit for a given package
// this script should expect a package name as parameter, request the related JSON of the test-unit, and run those tests
// we could use `packages` as parameter and use a string with commas as delimiters (to allow to test several packages in a row)


// todo : convert to AAA logic : Arrange, Act, Assert



function array_equals($array1, $array2) {
    $res = true;
    if(count($array1) != count($array2)) {
        return false;
    }
    foreach($array1 as $key => $value) {
        if(!isset($array2[$key]) || gettype($value) != gettype($array2[$key])) {
            $res = false;
        }
        else {
            if(gettype($value) == 'array') {
                $res = array_equals($value, $array2[$key]);
            }
            else if($value != $array2[$key]) {
                $res = false;
            }
        }
        if(!$res) break;
    }
    return $res;
}

$results = [];
$failing = [];

// todo
function do_assert($test){
    
}

foreach($tests as $id => $test) {
// todo : throw new Exception("invalid test structure", QN_ERROR_INVALID_PARAM);
// use a dedicated controller to check test unit structure validity

    if(isset($test['arrange']) && is_callable($test['arrange'])) {
        $precondition = $test['arrange']();
    }

    $result = $test['test']();
    $status = 'ok';

    if(in_array(gettype($result), $test['return'])) {

        if(isset($test['expected'])) {
            if(gettype($result) == gettype($test['expected'])) {
                if(gettype($result) == "array") {
                    if(!array_equals($result, $test['expected'])) {
                        $status= 'ko';
                    }
                }
                else {
                    if($result != $test['expected']) {
                        $status = 'ko';
                    }
                }
            }
            else {
                $status = 'ko';
            }
        }
    }
    else {
        $status = 'ko';
    }

    if($status == 'ko') $failing[] = $id;
    else {
        if(isset($test['rollback']) && is_callable($test['rollback'])) {
            $test['rollback']();
        }
    }

    $results[$id] = [
        'description'   => $test['description'],
        'status'        => $status,
        'result'        => $result
    ];

    if(isset($test['expected'])) {
        $results[$id]['expected'] = $test['expected'];
    }

    if(isset($test['arrange'])) {
        $results[$id]['arrange'] = $precondition;
    }
}

$body = ['result' => 'ok', 'log' => $results];
if(count($failing)) {
    $body['result'] = 'ko';
    $body['failed'] = $failing;
}

$providers['context']->httpResponse()->body($body)->send();

// force 1 as exit code if there is any failing test
if(count($failing)) {
    exit(1);
}
