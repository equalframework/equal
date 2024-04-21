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

    /*
    '4101' => array(
                    'description'       =>  "HTTP basic auth",
                    'return'            =>  array('integer', 'array'),
                    'test'              =>  function () {
                                                try {
                                                    $request = new HttpRequest("http://localhost/me");
                                                    $response = $request
                                                                ->header('Authorization', 'Basic '.base64_encode("user@equal.local:safe_pass"))
                                                                ->send();
                                                    return $response->body();
                                                }
                                                catch(\Exception $e) {
                                                    // possible raised Exception codes : QN_ERROR_INVALID_USER
                                                    $values = $e->getCode();
                                                }
                                                return $values;
                                            },
                        'expected'      =>  ['id' => 2, 'login' => 'user@equal.local', 'firstname' => 'User', 'lastname' => 'USER', 'language' => 'fr']
                    ),
*/
];