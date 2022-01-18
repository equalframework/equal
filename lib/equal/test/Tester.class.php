<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\test;

class Tester {
    
    // array of failing tests
    private $failing;
    // array with tests results
    private $results;
    // array with tests to perform
    private $tests;
    
    public function __construct($tests=[]) {
        $this->failing = [];
        $this->tests = $tests;
        $this->results = [];
    }
    
    public function toArray() {
        $result = [
            'result'        => 'ok',
            'count_test'    => count($this->tests),
            'log'           => $this->results
        ];
        // if something went wrong, append details
        if(count($this->failing)) {
            $result['result'] = 'ko';
            $result['failed'] = $this->failing;
        }
        return $result;
    }
    
    private static function array_equals($array1, $array2) {
        $res = true;
// #todo allow to make this optional (strict flag)        
        /*
        if(count($array1) != count($array2)) {
            return false;
        }
        */
        foreach($array1 as $key => $value) {
            if(!isset($array2[$key]) || gettype($value) != gettype($array2[$key])) {
                $res = false;
            }
            else {
                if(gettype($value) == 'array') {
                    $res = self::array_equals($value, $array2[$key]);
                }
                else if($value != $array2[$key]) {
                    $res = false;
                }
            }
            if(!$res) break;
        }
        return $res;
    }
    
    public function test($test_id=0) {
        
        foreach($this->tests as $id => $test) {
        // todo : throw new Exception("invalid test structure", QN_ERROR_INVALID_PARAM);
        // use a dedicated controller to check test unit structure validity

            // if a specific test ID was given, ignore other tests
            if($test_id && $id != $test_id) continue;

            if(isset($test['arrange']) && is_callable($test['arrange'])) {
                $precondition = $test['arrange']();
            }

            $result = null;
            $success = true;

            if(isset($test['test']) && is_callable($test['test'])) {
                $result = $test['test']();
            }
            elseif(isset($test['act']) && is_callable($test['act'])) {
                $result = $test['act']();
            }
            

            if(in_array(gettype($result), (array) $test['return'])) {

                if(isset($test['expected'])) {
                    if(gettype($result) == gettype($test['expected'])) {
                        if(gettype($result) == "array") {
                            if(!self::array_equals($test['expected'], $result)) {
                                $success = false;
                            }
                        }
                        else {
                            if($result != $test['expected']) {
                                $success = false;
                            }
                        }
                    }
                    else {
                        $success = false;
                    }
                }
                else if(isset($test['assert']) && is_callable($test['assert'])) {
                    $success = $test['assert']($result);
                }
            }
            else {
                $success = false;
            }

            if(!$success) {
                $this->failing[] = $id;
            }
            else {
                if(isset($test['rollback']) && is_callable($test['rollback'])) {
                    $test['rollback']();
                }
            }

            $this->results[$id] = [
                'description'   => $test['description'],
                'status'        => $success?'ok':'ko',
                'result'        => $result
            ];

            if(isset($test['expected'])) {
                $this->results[$id]['expected'] = $test['expected'];
            }

            if(isset($test['arrange'])) {
                $this->results[$id]['arrange'] = $precondition;
            }
        }

        return $this;
    }
}