<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\http\HttpRequest;
use core\User;
use core\Group;

$providers = eQual::inject(['context', 'orm', 'auth', 'access']);

// todo : convert to AAA logic : Arrange, Act, Assert (, Restore)

$tests = [

    //1xxx : calls related to the ObjectManger instance
    '1000' => array(
                'description'       => "Get instance of the object Manager",
                'return'            => array('boolean'),
                'expected'          => true,
                'test'              => function (){
                                            $om = &ObjectManager::getInstance();
                                            return (is_object($om) && ($om instanceof equal\orm\ObjectManager));
                                        }
                ),

    '1100' => array(
                'description'       =>  "Check uniqueness of ObjectManager instance",
                'return'            =>  array('boolean'),
                'expected'          =>  true,
                'test'              =>  function (){
                                            $om1 = &ObjectManager::getInstance();
                                            $om2 = &ObjectManager::getInstance();
                                            return ($om1 === $om2);
                                        }
                ),

    //21xx : calls related to the read method
    // @signature   function read($uid, $class, $ids, $fields=NULL, $lang='en')
    // @return      mixed (int or array) error code OR resulting associative array

    '2100' => array(
                'description'       =>  "Requesting User object by passing an array holding a unique id",
                'return'            =>  array('integer', 'array'),
                'expected'          =>  array(
                                        '1' => array(
                                                'language'  => 'en',
                                                'firstname' => 'Root',
                                                'lastname'  => 'USER'
                                              )
                                        ),
                'test'              =>  function (){
                                            $res = [];
                                            $om = ObjectManager::getInstance();
                                            $objects = $om->read('core\User', [QN_ROOT_USER_ID], array('id', 'language','firstname','lastname'));
                                            foreach($objects as $oid => $object) {
                                                $res[$oid] = $object->toArray();
                                            }
                                            return $res;
                                        }
                ),

    '2101' => array(
                    'description'       =>  "Requesting User object by passing an integer as id",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(
                                            '1' => array(
                                                    'language'  => 'en',
                                                    'firstname' => 'Root',
                                                    'lastname'  => 'USER'
                                                  )
                                            ),
                    'test'              =>  function (){
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', QN_ROOT_USER_ID, array('language','firstname','lastname'));
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;
                                            }
                    ),
    '2102' => array(
                    'description'       =>  "Requesting User object by passing a string as id",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(
                                            '1' => array(
                                                    'language'  => 'en',
                                                    'firstname' => 'Root',
                                                    'lastname'  => 'USER'
                                                  )
                                            ),
                    'test'              =>  function (){
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', (string) QN_ROOT_USER_ID, array('language','firstname','lastname'));
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;
                                            }
                    ),

    '2103' => array(
                    'description'       =>  "Requesting User object by giving a non-existing integer id",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(),
                    'test'              =>  function (){
                                                $om = ObjectManager::getInstance();
                                                return $om->read('core\User', 0, array('language','firstname','lastname'));
                                            }
                    ),

    '2104' => array(
                    'description'       =>  "Requesting User object by passing an array containing an invalid id",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(
                                            '1' => [
                                                    'language'  => 'en',
                                                    'firstname' => 'Root',
                                                    'lastname'  => 'USER'
                                                   ]
                                            ),
                    'test'              =>  function () {
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', array(0, QN_ROOT_USER_ID), array('language','firstname','lastname'));
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;

                                            }
                    ),

    '2105' => array(
                    'description'       =>  "Call ObjectManager::read with empty value for \$ids parameter : empty array",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(),
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->read('core\User', array(), array('language','firstname','lastname'));
                                            }
                    ),

    '2110' => array(
                    'description'       =>  "Call ObjectManager::read with missing \$ids parameters",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  [],
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->read('core\User');
                                            }
                    ),
    '2120' => array(
                    'description'       =>  "Call ObjectManager::read with wrong \$ids parameters",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(),
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->read('core\User', 0);
                                            }
                    ),
    '2130' => array(
                    'description'       =>  "Call ObjectManager::read some non-existing object from non-existing class",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  QN_ERROR_UNKNOWN_OBJECT,
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->read('core\Foo', array('1'), array('bar'));
                                            }
                    ),

    '2140' => array(
                    'description'       =>  "Call ObjectManager::read with a string as field",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array(
                                            '1' => array(
                                                    'firstname' => 'Root'
                                                  )
                                            ),
                    'test'              =>  function () {
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', array('1'), 'firstname');
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;
                                            }
                    ),
    '2150' => array(
                    'description'       =>  "Call ObjectManager::read with wrong \$fields value : non-existing field name",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  [
                                                '1' => []
                                            ],
                    'test'              =>  function () {
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', array('1'), array('foo'));
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;
                                            }
                    ),
    '2151' => array(
                    'description'       =>  "Call ObjectManager::read with wrong \$fields value : non-existing field name",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array('1' => array('firstname' => 'Root') ),
                    'test'              =>  function () {
                                                $res = [];
                                                $om = ObjectManager::getInstance();
                                                $objects = $om->read('core\User', array('1'), array('foo', 'firstname'));
                                                foreach($objects as $oid => $object) {
                                                    $res[$oid] = $object->toArray();
                                                }
                                                return $res;
                                            },
                    ),


    //22xx : calls related to the create method
    '2210' => array(
                    'description'       =>  "Create a user (no validation)",
                    'return'            =>  array('integer'),
                    'test'              =>  function () {
                                                global $dummy_user_id;
                                                $om = ObjectManager::getInstance();
                                                $dummy_user_id = $om->create('core\User', [
                                                        'login'     => 'dummy@example.com',
                                                        'password'  => md5('test'),
                                                        'firstname' => 'foo',
                                                        'lastname'  => 'bar'
                                                    ]);
                                                return $dummy_user_id;
                                            }
                    ),

    '2220' => [
                    'description'       =>  "Create a group (no validation)",
                    'return'            =>  array('integer'),
                    'act'               =>  function () {
                                                $om = ObjectManager::getInstance();
                                                $group_id = $om->create('core\Group', ['name' => 'test']);
                                                return $group_id;
                                            },
                    'assert'            =>  function($result) {
                                                return ($result >= 1);
                                            },
                    'rollback'          =>  function() {
                                                Group::search(['name', '=', 'test'])->delete(true);
                                            }

        ],



    //23xx : calls related to the write method

    //24xx : calls related to the remove method
    '2401' => array(
                    'description'       =>  "Remove a user (no validation)",
                    'return'            =>  array('integer', 'array'),
                    'assert'            =>  function($result) {
                                                return ($result > 0);
                                            },
                    'act'               =>  function () {
                                                $om = ObjectManager::getInstance();
                                                $dummy_user_id = $om->search('core\Group', ['login', '=', 'dummy@example.com']);
                                                return $om->remove('core\User', $dummy_user_id, true);
                                            }
                    ),

    //25xx : calls related to the search method
    // @signature : public function search($object_class, $domain=NULL, $order='id', $sort='asc', $start='0', $limit='0', $lang='en') {
    // @return : mixed (integer or array)
    '2501' => array(
                    'description'       =>  "Search an object with valid clause 'ilike'",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  array('2'),
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->search('core\Group', array(array(array('name', 'ilike', '%Users%'))));
                                            }
                    ),
    '2502' => array(
                    'description'       =>  "Search an object with invalid clause 'ilike' (non-existing field)",
                    'return'            =>  array('integer', 'array'),
                    'expected'          =>  QN_ERROR_INVALID_PARAM,
                    'test'              =>  function () {
                                                $om = ObjectManager::getInstance();
                                                return $om->search('core\Group', array(array(array('badname', 'ilike', '%Default%'))));
                                            }
                    ),
    '2510' => array(
                    'description'       =>  "Search for some object : clause 'contains' on one2many field",
                    'return'            =>  array('boolean'),
                    'expected'          =>  true,
                    'test'              =>  function (){
    // todo
                                                return true;
                                            },
                    ),
    '2520' => array(
                    'description'       =>  "Search for some object : clause 'contains' on one2many field (using a foreign key different from 'id')",
                    'return'            =>  array('boolean'),
                    'expected'          =>  true,
                    'test'              =>  function () {
    // todo
                                                return true;
                                            }
                    ),
    '2530' => array(
                    'description'       =>  "Search for some object : clause 'contains' on many2one field",
                    'return'            =>  array('boolean'),
                    'expected'          =>  true,
                    'test'              =>  function () {
    // todo
                                                return true;
                                            }
                    ),

    // calls related to authentication
    '2610' => array(
                    'description'       =>  "Authenticate: return the identifier of a given user: called with CLI, should return QN_ROOT_USER_ID.",
                    'return'            =>  array('integer'),
                    'expected'          =>  QN_ROOT_USER_ID,
                    'test'              =>  function () use($providers) {
                                                try {
                                                    $providers['auth']->authenticate('root@equal.local', 'secure_password');
                                                    $values = $providers['auth']->userId();
                                                }
                                                catch(Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_INVALID_PARAM, QN_ERROR_INVALID_USER
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            }
                    ),

    '2620' => array(
                    'description'       =>  "Search for some object : clause 'contains' on many2many field",
                    'return'            =>  array('integer', 'array'),
                    'arrange'           =>  function () use($providers) {
                                                try {
                                                    $providers['auth']->authenticate('user@equal.local', 'safe_pass');
                                                    // grant READ operation on all classes
                                                    $providers['access']->grant(QN_R_READ);

                                                    $values = User::search(array(array('groups_ids', 'contains', array(1, 2, 3))))
                                                          ->read(['id', 'login'])
                                                          ->get();
                                                }
                                                catch(Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'assert'            =>  function($result) {
                                                return is_array($result) && count($result) == 2 && (
                                                    count(array_diff(['id' => 1, 'login' => 'root@equal.local'], (array) $result['1'])) == 0
                                                 && count(array_diff(['id' => 2, 'login' => 'user@equal.run'], (array) $result['2'])) == 0
                                                );
                                            }
                    ),

    '2631' => array(
                    'description'       =>  "Add a user to a given group",
                    'return'            =>  array('integer', 'array'),
                    'act'               =>  function () use($providers) {
                                                try {
                                                    // grant READ operation on all classes
                                                    $providers['access']->addGroup(2);

                                                    $values = User::search( array(array('groups_ids', 'contains', [2])) )
                                                            ->read(['id', 'login'])
                                                            ->get();
                                                }
                                                catch(Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'assert'            =>  function($result) {
                                                return (
                                                    count(array_diff(['id' => 1, 'login' => 'root@equal.local'], (array) $result['1'])) == 0
                                                );
                                            }
                    ),

    // 3xxx methods : related to Collections calls
    '3001' => array(
                    'description'       =>  "Check uniqueness of services instances",
                    'return'            =>  array('boolean', 'array'),
                    'expected'          =>  true,
                    'test'              =>  function () use($providers) {

                                                $auth1 = $providers['context']->container->get('auth');
                                                $auth2 = $providers['auth'];

                                                $access1 = $providers['context']->container->get('access');
                                                $access2 = $providers['access'];

                                                return ( $auth1 == $auth2 && $access1 == $access2);
                                            }
                    ),

    '3101' => array(
                    'description'       =>  "Search for an existing user object using Collection (result as map)",
                    'return'            =>  array('integer', 'array'),
                    'act'               =>  function () {
                                                try {
                                                    $values = User::search(['login', 'like', 'user@equal.local'])
                                                              ->read(['login'])
                                                              ->get();
                                                }
                                                catch(\Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'assert'            =>  function($result) {
                                                return (
                                                    count($result) &&
                                                    count(array_diff(['id' => 2, 'login' => 'user@equal.local'], (array) $result[2])) == 0
                                                );
                                            }
                    ),
    '3102' => array(
                    'description'       =>  "Search for an existing user object using Collection (result as array)",
                    'return'            =>  array('integer', 'array'),
                    'act'               =>  function () {
                                                try {
                                                    $values = User::search(['login', '=', 'user@equal.local'])
                                                              ->read(['login'])
                                                              ->get(true);
                                                }
                                                catch(\Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'assert'            =>  function($result) {
                                                return (
                                                    count($result) &&
                                                    count(array_diff(['id' => 2, 'login' => 'user@equal.local'], (array) $result[0])) == 0
                                                );
                                            }
                    ),

    '3103' => array(
                    'description'       =>  "Search for a new user object using Collection (result as array)",
                    'return'            =>  array('integer', 'array'),
                    'arrange'           =>  function() use($providers) {
                                                try {
                                                    $providers['access']->grant(QN_R_CREATE|QN_R_DELETE);
                                                    $values = User::create(['login' => 'test@equal.run', 'password' => 'test1234'])->ids();
                                                }
                                                catch(\Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'rollback'          =>  function() use($providers) {
                                                $om = $providers['orm'];
                                                $ids = $om->search('core\User', [['login', '=', 'test@equal.run']]);
                                                $om->remove('core\User', $ids, true);
                                                $providers['access']->revoke(QN_R_CREATE|QN_R_DELETE);
                                            },
                    'act'               =>  function () {
                                                try {
                                                    $values = User::search(['login', '=', 'test@equal.run'])
                                                              ->read(['login'])
                                                              ->first(true);
                                                }
                                                catch(\Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_NOT_ALLOWED
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                    'expected'          =>  ['login' => 'test@equal.run']
                    ),

];