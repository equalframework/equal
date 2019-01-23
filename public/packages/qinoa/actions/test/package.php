<?php
/**
*    This file is part of the Qinoa project <http://www.cedricfrancoys.be/qinoa>
*
*    Copyright (C) Cedric Francoys, 2015, Yegen
*    Some Rights Reserved, GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\orm\ObjectManager;
use qinoa\test\Tester;

use core\User;
use core\Group;

list($params, $providers) = eQual::announce([
    'description'   => 'Test unit for Qinoa and Core packages. This unit assumes that a DB connection is available and config vars are set accordingly.',
    'params'        => [
        'package'   => [
            'description'   =>  "Name of the package on which to perform test units",
            'type'          =>  'string',
            'default'       =>  '*'
        ]
    ],
    'providers'     => ['context']
]);


$result = [];

// 2) populate core_user table with demo data
$tests_path = "packages/{$params['package']}/tests";
if(is_dir($tests_path)) {
    
    foreach (glob($tests_path."/*.php") as $filename) {
        include($filename);
        $tester = new Tester($tests);
        $body = $tester->test()->toArray();
        if(isset($body['failed'])) {
            $providers['context']->httpResponse()->body($body)->send();
            // force 1 as exit code if there is any failing test
            exit(1);            
        }
        $result[basename($filename)] = $body;
    }    

}


$providers['context']->httpResponse()->body($result)->send();