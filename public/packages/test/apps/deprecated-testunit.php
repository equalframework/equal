<?php
/**
*    This file is part of the Qinoa project <<<http://www.cedricfrancoys.be/qinoa>
*
*    Copyright (C) Cedric Francoys, 2015, Yegen
*    Some Rights Reserved, GNU GPL 3 license <http://www.gnu.org/licenses/>
*/


/*
* file: packages/test/apps/testunit.php
*
*
*/

// the dispatcher (index.php) is in charge of setting the context and should include the easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

use config\QNLib as QNLib;
use easyobject\orm\ObjectManager as ObjectManager;

set_silent(true);


$params = QNLib::announce(
    array(
    'description'    =>    "Testing unit to ensure API calls matches specs in any situation.",
    'params'         =>    array(
                        'lang'    => array(
                                    'description '=> 'Specific language for multilang field.',
                                    'type' => 'string',
                                    'default' => DEFAULT_LANG
                                    )
                        )
    )
);






$tests = array(
            //1xxx : calls related to the ObjectManger instance
            '1000' => array(
                            'description'       => "Get instance of the object Manager",
                            'return'            => array('boolean'),
                            'expected'          => true,
                            'test'              => function (){
                                                        $om = &ObjectManager::getInstance();
                                                        return (is_object($om));
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
// todo : first using direct calls to OM, then using Collections

            //2xxx : calls related to the read method
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
                                                        return read('core\User', array(0, ROOT_USER_ID), array('language','firstname','lastname'));
                                                    },
                            ),

             '2105' => array(
                            'description'       => "Calling read method with empty value for \$ids parameter : empty array",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(),
                            'test'              => function (){
                                                        return read('core\User', array(), array('language','firstname','lastname'));
                                                    },
                            ),

            '2110' => array(
                            'description'       => "Calling read method with missing parameters",
                            'return'            => array('integer', 'array'),
                            'expected'          => array(),
                            'test'              => function (){
                                                        return read('core\User');
                                                    },
                            ),

            '2200' => array(
                            'description'       => "Trying to read some unexisting object from non-existing class",
                            'return'            => array('integer', 'array'),
                            'expected'          => UNKNOWN_OBJECT,
                            'test'              => function (){
                                                        return read('core\Foo', array('1'), array('bar'));
                                                    },
                            ),

            '2300' => array(
                            'description'       => "Calling read method with a string as field",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => array(
                                                   '1' => array(
                                                            'firstname' => 'root'
                                                          )
                                                   ),                            
                            'test'              => function (){
                                                        return read('core\User', array('1'), 'firstname');
                                                    },
                            ),
            '2310' => array(
                            'description'       => "Calling read method with wrong \$fields value : unexisting field name",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => array('1' => array() ),
                            'test'              => function (){
                                                        return read('core\User', array('1'), array('foo'));
                                                },
                            ),
            '2320' => array(
                            'description'       => "Calling read method on related \$fields : many2one, 1 step path",
                            'return'            => array('integer', 'array'),                            
                            'expected'          => array(
                                                   '1' => array(
                                                            'group_id.name' => 'default'
                                                          )
                                                   ),                               
                            'test'              => function (){
                                                        return read('core\Permission', array(1), array('group_id.name'));
                                                    },
                            ),

                           

            //3xxx : calls related to the search method
            // @signature : public function search($object_class, $domain=NULL, $order='id', $sort='asc', $start='0', $limit='0', $lang=DEFAULT_LANG) {
            // @return : mixed (integer or array)
            '3000' => array(
                            'description'       => "Trying to search for some object : clause 'ilike'",
                            'return'            => array('integer', 'array'),
                            'expected'          => array('1'),
                            'test'              => function (){
                                                        return search('core\Group', array(array(array('name', 'ilike', '%Default%'))));
                                                    },
                            ),
            '3001' => array(
                            'description'       => "Trying to search for some object with non-existing field in clause",
                            'return'            => array('integer', 'array'),
                            'expected'          => UNKNOWN_OBJECT,
                            'test'              => function (){
                                                        return search('core\Group', array(array(array('nadme', 'ilike', '%Default%'))));
                                                    },
                            ),                            
/*                            
            '3100' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on one2many field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('attributes_ids', 'contains', array(1, 2))));
                                                        return $values;
                                                    },
                            ),
            '3110' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on one2many field (using a foreign key different from 'id')",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('attributes_types', 'contains', array('author', 'editor'))));
                                                        return $values;
                                                    },
                            ),
            '3120' => array(
                            'description'        => "Trying to search for some object : clause 'contains' on many2one field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('parent_id', '=', '1')));
                                                        return $values;
                                                    },
                            ),
            '3120' => array(
                            'description'        => "Trying to search for some object : clause contain on many2many field",
                            'expected'    => true,
                            'test'                => function (){
                                                        $values = search('knine\Article', array(array('labels_ids', 'contains', array(1, 2, 3))));
                                                        return $values;
                                                }    ,
                            ),
            '9999' => array(
                            'description'        => "tests",
                            'expected'    => array(),
                            'test'                => function (){
                                                        $values = read('core\User', array('0'));
                                                        $user_id = $values[0]['id'];
                                                        $values = read('core\User', array($user_id));
                                                        //$values = &$om->read('school\Lesson', array(1), array('students_ids'));
                                                        //$values = &$om->read('school\Lesson', array(1), array('teacher_courses_ids'));
                                                        //$values = &$om->read('school\Lesson', array(1), array('teacher_id'));
                                                        //$values = &$om->read('school\Student', array(2), array('lessons_ids'));

                                                        return $values;
                                                    },
                            ),
*/

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

    if(in_array(gettype($result), $test['return']) && gettype($result) == gettype($test['expected'])) {
        if(gettype($result) == "array") {
            if(array_equals($result, $test['expected'])) {
                echo "ok";
            }
            else {
                echo '<b style="color: red;">KO</b>';
            }

        }
        else {
            if($result == $test['expected']) {
                echo "ok";
            }
            else echo '<b style="color: red;">KO</b>';
        }
    }
    else {
        echo '<b style="color: red;">KO</b>';
    }
    echo "  (result=".json_encode($result).")";

    echo "\n";
}

echo "</pre>";