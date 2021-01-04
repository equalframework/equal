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

list($params, $providers) = announce([
    'description'   => 'Check presence of test units for given package and run them, if any.',
    'params'        => [
        'package'   => [
            'description'   =>  "Name of the package on which to perform test units",
            'type'          =>  'string',
            'default'       =>  '*'
        ],
        'logs'      => [
            'description'   =>  "Embed logs in result",
            'type'          =>  'boolean',
            'default'       =>  false
        ]
    ],
    'providers'     => ['context']
]);


$result = [];

// 2) populate core_user table with demo data
$failed = false;
$tests_path = "packages/{$params['package']}/tests";
if(is_dir($tests_path)) {
    
    foreach (glob($tests_path."/*.php") as $filename) {
        include($filename);
        $tester = new Tester($tests);
        $body = $tester->test()->toArray();
        if(isset($body['failed'])) {
            $failed = true;
        }
        $result[basename($filename, '.php')] = $body;
    }    
}
if($params['logs']) {
    $result['logs'] = file_get_contents(QN_LOG_STORAGE_DIR.'/error.log').file_get_contents(QN_LOG_STORAGE_DIR.'/qn_error.log');            
}

$providers['context']->httpResponse()
                     ->body($result)
                     ->send();
                     
if($failed) {
    exit(1);
}