<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2019, Brussels
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\test;

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
            'result'    => 'ok',
            'log'       => $this->results
        ];

        if(count($this->failing)) {
            $result['result'] = 'ko';
            $result['failed'] = $this->failing;
        }
        return $result;
    }
    
    private static function array_equals($array1, $array2) {
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
    
    public function test() {
        
        foreach($this->tests as $id => $test) {
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
                            if(!self::array_equals($result, $test['expected'])) {
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

            if($status == 'ko') $this->failing[] = $id;
            else {
                if(isset($test['rollback']) && is_callable($test['rollback'])) {
                    $test['rollback']();
                }
            }

            $this->results[$id] = [
                'description'   => $test['description'],
                'status'        => $status,
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
