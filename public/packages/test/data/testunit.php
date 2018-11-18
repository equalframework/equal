<?php
/**
*    This file is part of the Qinoa project <http://www.cedricfrancoys.be/qinoa>
*
*    Copyright (C) Cedric Francoys, 2015, Yegen
*    Some Rights Reserved, GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib as QNLib;
use qinoa\orm\ObjectManager;


use core\User;
use core\Group;


$tests = array(
            //0xxx : calls related to QNLib methods
            '0101' => array(
                            'description'       => "Get auth provider",
                            'return'            => array('boolean'),
                            'expected'          => true,
                            'test'              => function (){
                                                        list($params, $providers) = QNLib::announce([
                                                            'providers' => ['qinoa\auth\AuthenticationManager']
                                                        ]);
                                                        $auth = $providers['qinoa\auth\AuthenticationManager'];
                                                        return ( is_object($auth) && ($auth instanceof qinoa\auth\AuthenticationManager) );
                                                    },
            
                            ),
            '0102' => array(
                            'description'       => "Get auth provider using custom registered name",
                            'return'            => array('boolean'),
                            'expected'          => true,
                            'test'              => function (){
                                                        list($params, $providers) = QNLib::announce([
                                                            'providers' => ['@@testAuth' => 'qinoa\auth\AuthenticationManager']
                                                        ]);
                                                        $auth = $providers['@@testAuth'];
                                                        return ( is_object($auth) && ($auth instanceof qinoa\auth\AuthenticationManager));
                                                    },
            
                            ),

            //1xxx : calls related to the ObjectManger instance
            '1000' => array(
                            'description'       => "Get instance of the object Manager",
                            'return'            => array('boolean'),
                            'expected'          => true,
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return (is_object($om) && ($om instanceof qinoa\orm\ObjectManager));
                                                    },
                            ),

            '1100' => array(
                            'description'       => "Check uniqueness of ObjectManager instance",
                            'return'            => array('boolean'),
                            'expected'          => true,
                            'test'              => function (){
                                                        $om1 = &ObjectManager::getInstance();
                                                        $om2 = &ObjectManager::getInstance();
                                                        return ($om1 === $om2);
                                                    },
                            ),

            //21xx : calls related to the read method
            // @signature   function read($uid, $class, $ids, $fields=NULL, $lang=DEFAULT_LANG)
            // @return      mixed (int or array) error code OR resulting associative array

            '2100' => array(
                            'description'       => "Requesting User object by passing an id array holding a unique id",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(
                                                   '1' => array(
                                                            'language'  => 'en',
                                                            'firstname' => 'root',
                                                            'lastname'  => '@system'
                                                          )
                                                   ),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', [ROOT_USER_ID], array('language','firstname','lastname'));
                                                   },
                            ),

            '2101' => array(
                            'description'       => "Requesting User object by passing an integer as id",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(
                                                   '1' => array(
                                                            'language'  => 'en',
                                                            'firstname' => 'root',
                                                            'lastname'  => '@system'
                                                          )
                                                   ),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', ROOT_USER_ID, array('language','firstname','lastname'));
                                                    },
                            ),
            '2102' => array(
                            'description'       => "Requesting User object by pasing a string as id",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(
                                                   '1' => array(
                                                            'language'  => 'en',
                                                            'firstname' => 'root',
                                                            'lastname'  => '@system'
                                                          )
                                                   ),                            
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', (string) ROOT_USER_ID, array('language','firstname','lastname'));
                                                    },
                            ),
                            
            '2103' => array(
                            'description'       => "Requesting User object by giving a non-existing integer id",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', 0, array('language','firstname','lastname'));
                                                    },
                            ),

            '2104' => array(
                            'description'       => "Requesting User object by passing an array containing an invalid id",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(
                                                   '1' => array(
                                                            'language'  => 'en',
                                                            'firstname' => 'root',
                                                            'lastname'  => '@system'
                                                          )
                                                   ),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', array(0, ROOT_USER_ID), array('language','firstname','lastname'));
                                                    },
                            ),

            '2105' => array(
                            'description'       => "Call ObjectManager::read with empty value for \$ids parameter : empty array",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', array(), array('language','firstname','lastname'));
                                                    },
                            ),

            '2110' => array(
                            'description'       => "Call ObjectManager::read with missing \$ids parameters",
                            'return'            => array('integer', 'array'),
                            'expected'          => MISSING_PARAM,
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User');
                                                    },
                            ),
            '2120' => array(
                            'description'       => "Call ObjectManager::read with wrong \$ids parameters",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', 0);
                                                    },
                            ),
            '2130' => array(
                            'description'       => "Call ObjectManager::read some unexisting object from non-existing class",
                            'return'            => array('integer', 'array'),
                            'expected'          => UNKNOWN_OBJECT,
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\Foo', array('1'), array('bar'));
                                                    },
                            ),

            '2140' => array(
                            'description'       => "Call ObjectManager::read with a string as field",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => array(
                                                   '1' => array(
                                                            'firstname' => 'root'
                                                          )
                                                   ),                            
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', array('1'), 'firstname');
                                                    },
                            ),
            '2150' => array(
                            'description'       => "Call ObjectManager::read with wrong \$fields value : unexisting field name",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => [
                                                        '1' => []
                                                   ],
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', array('1'), array('foo'));
                                                },
                            ),
            '2151' => array(
                            'description'       => "Call ObjectManager::read with wrong \$fields value : unexisting field name",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => array('1' => array('firstname' => 'root') ),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->read('core\User', array('1'), array('foo', 'firstname'));
                                                },
                            ),                            
                            
                            
            //22xx : calls related to the create method
            '2201' => array(
                            'description'       => "Create a user (no validation)",
                            'return'            => array('integer'),                            
                            'test'              => function () {
                                                        global $dummy_user_id;
                                                        $om = &ObjectManager::getInstance();
                                                        $dummy_user_id = $om->create('core\User', [
                                                                                        'login'     => 'dummy@example.com',
                                                                                        'password'  => md5('test'),
                                                                                        'firstname' => 'foo',
                                                                                        'lastname'  => 'bar'
                                                                                        ]);
                                                        return $dummy_user_id;
                                                    },
                            ),

            //23xx : calls related to the write method
            
            //24xx : calls related to the remove method            
            '2401' => array(
                            'description'       => "Create a user (no validation)",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(&$GLOBALS['dummy_user_id']), 
                            'test'              => function () {
                                                        global $dummy_user_id;
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->remove('core\User', $dummy_user_id);
                                                    },
                            ),
                            
            //25xx : calls related to the search method
            // @signature : public function search($object_class, $domain=NULL, $order='id', $sort='asc', $start='0', $limit='0', $lang=DEFAULT_LANG) {
            // @return : mixed (integer or array)
            '2500' => array(
                            'description'       => "Search an object with valid clause 'ilike'",
                            'return'            => array('integer', 'array'),
                            'expected'          => array('1'),
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->search('core\Group', array(array(array('name', 'ilike', '%Default%'))));
                                                    },
                            ),
            '2501' => array(
                            'description'       => "Search an object with invalid clause 'ilike' (non-existing field)",
                            'return'            => array('integer', 'array'),
                            'expected'          => INVALID_PARAM,
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return $om->search('core\Group', array(array(array('badname', 'ilike', '%Default%'))));
                                                    },
                            ),                            
/*                            
            '2310' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on one2many field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('attributes_ids', 'contains', array(1, 2))));
                                                        return $values;
                                                    },
                            ),
            '2320' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on one2many field (using a foreign key different from 'id')",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('attributes_types', 'contains', array('author', 'editor'))));
                                                        return $values;
                                                    },
                            ),
            '2330' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on many2one field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('parent_id', '=', '1')));
                                                        return $values;
                                                    },
                            ),
            '2340' => array(
                            'description'        => "Trying to search for some object : clause contain on many2many field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('labels_ids', 'contains', array(1, 2, 3))));
                                                        return $values;
                                                }    ,
                            ),
*/
            // 3xxx methods : related to Collections calls
            '3000' => array(
                            'description'       => "Search a user object using Collection (result as map)",
                            'return'            => array('integer', 'array'),
                            'expected'          => array('3' => ['login' => 'cedricfrancoys@gmail.com']),
                            'test'              => function () {
                                                        try {
                                                            $values = User::search(['login', 'like', 'cedricfrancoys@gmail.com'])
                                                                      ->read(['login'])
                                                                      ->get();
                                                        }
                                                        catch(\Exception $e) {
                                                            // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                            $values = $e->getCode();
                                                        }
                                                        return $values;
                                                }
                            ),
            '3001' => array(
                            'description'       => "Search a user object using Collection (result as array)",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(['login' => 'cedricfrancoys@gmail.com']),
                            'test'              => function () {
                                                        try {
                                                            $values = User::search(['login', 'like', 'cedricfrancoys@gmail.com'])
                                                                      ->read(['login'])
                                                                      ->get(true);
                                                        }
                                                        catch(\Exception $e) {
                                                            // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                            $values = $e->getCode();
                                                        }
                                                        return $values;
                                                }
                            ),                            

);

function array_equals($array1, $array2) {
    $res = true;
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

echo "<pre>";

foreach($tests as $id => $test) {
    echo "{ $id } :  {$test['description']} - ";
    $result = $test['test']();

    if(in_array(gettype($result), $test['return'])) {
        
        if(isset($test['expected'])) {
            if(gettype($result) == gettype($test['expected'])) {
                if(gettype($result) == "array") {
                    if(array_equals($result, $test['expected'])) {
                        echo '<b style="color: green;">OK</b>';
                    }
                    else {
                        echo '<b style="color: red;">KO</b>';
                        $fails = true;
                    }
                }
                else {
                    if($result == $test['expected']) {
                        echo '<b style="color: green;">OK</b>';
                    }
                    else {
                        echo '<b style="color: red;">KO</b>';
                        $fails = true;                        
                    }
                }
            }
            else {
                echo '<b style="color: red;">KO</b>';
                $fails = true;
            }
        }
        else {
            echo '<b style="color: green;">OK</b>';
        }
    }
    else {
        echo '<b style="color: red;">KO</b>';
        $fails = true;
    }
    echo "  (result=".json_encode($result)."), (expected=".json_encode($test['expected']).")";

    echo "\n";
}

echo "</pre>";

if($fails) {
    exit(1);
}