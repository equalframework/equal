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

    private static function array_equals($array1, $array2, $strict=false) {
        $res = true;
        if($strict && count($array1) != count($array2)) {
            return false;
        }
        foreach($array1 as $key => $value) {
            if(!isset($array2[$key]) || gettype($value) != gettype($array2[$key])) {
                $res = false;
            }
            else {
                if(gettype($value) == 'array') {
                    $res = self::array_equals($value, $array2[$key], $strict);
                }
                elseif($value != $array2[$key]) {
                    $res = false;
                }
            }
            if(!$res) {
                break;
            }
        }
        return $res;
    }

    public function test($test_id=0) {

        foreach($this->tests as $id => $test) {
            // #todo - throw new Exception("invalid test structure", QN_ERROR_INVALID_PARAM);
            // use a dedicated controller to check test unit structure validity

            // if a specific test ID was given, ignore other tests
            if($test_id && $id != $test_id) {
                continue;
            }

            $this->results[$id] = [
                'description'   => $test['description'],
                'logs'          => []
            ];

            $args = [];
            $result = null;
            $success = true;
            $start = time();

            if(isset($test['arrange']) && is_callable($test['arrange'])) {
                try {
                    $precondition = $test['arrange']();
                    $args = [$precondition];
                }
                catch(\Exception $e) {
                    $this->results[$id]['logs'][] = "Error while performing `arrange()`: ".$e->getMessage();
                    throw new \Exception("test {$id} raised an unexpected exception", QN_ERROR_UNKNOWN);
                }
            }

            if(isset($test['test']) && is_callable($test['test'])) {
                try {
                    $result = call_user_func_array($test['test'], $args);
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
                    }
                    else {
                        $success = false;
                    }
                }
                catch(\Exception $e) {
                    $success = false;
                    $this->results[$id]['logs'][] = $e->getMessage();
                }
            }
            elseif(isset($test['act']) && is_callable($test['act'])) {
                try {
                    $result = call_user_func_array($test['act'], $args);
                    if(isset($test['assert']) && is_callable($test['assert'])) {
                        $success = call_user_func_array($test['assert'], [$result]);
                    }
                }
                catch(\Exception $e) {
                    $success = false;
                    $this->results[$id]['logs'][] = $e->getMessage();
                }
            }
            elseif(isset($test['assert']) && is_callable($test['assert'])) {
                try {
                    $success = call_user_func_array($test['assert'], $args);
                }
                catch(\Exception $e) {
                    $success = false;
                    $this->results[$id]['logs'][] = $e->getMessage();
                }
            }

            if(!$success) {
                $this->failing[] = $id;
            }

            if(isset($test['rollback'])) {
                if(is_callable($test['rollback'])) {
                    try {
                        $test['rollback']();
                    }
                    catch(\Exception $e) {
                        $this->results[$id]['logs'][] = "Error while performing `rollback()`: ".$e->getMessage();
                    }
                }
                elseif($test['rollback'] == 'auto') {
                    try {
                        $provider = \eQual::inject(['orm']);

                        /** @var \equal\orm\ObjectManager $orm */
                        $orm = $provider['orm'];

                        $log_ids = $orm->search('core\Log', ['created', '>=', $start]);
                        $logs = $orm->read('core\Log', $log_ids, ['action', 'object_class', 'object_id']);

                        $map_object_to_delete = [];
                        foreach($logs as $log) {
                            if($log['action'] != 'create') {
                                continue;
                            }
                            if(!isset($map_object_to_delete[$log['object_class']])) {
                                $map_object_to_delete[$log['object_class']] = [];
                            }
                            $map_object_to_delete[$log['object_class']][] = $log['object_id'];
                        }

                        foreach($map_object_to_delete as $class => $ids) {
                            $orm->delete($class, $ids, true);
                        }

                        $orm->delete('core\Log', $log_ids, true);
                    } catch(\Exception $e) {
                        $this->results[$id]['logs'][] = "Error while performing auto `rollback`: ".$e->getMessage();
                    }
                }
            }

            $this->results[$id]['status'] = $success?'ok':'ko';
            $this->results[$id]['result'] = (gettype($result) == 'object')?'(object)':$result;

            if(isset($test['expected'])) {
                $this->results[$id]['expected'] = $test['expected'];
            }

            if(isset($test['arrange'])) {
                $this->results[$id]['arrange'] = (gettype($precondition) == 'object')?'(object)':$precondition;
            }
        }

        return $this;
    }
}