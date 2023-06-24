<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\test\Tester;

list($params, $providers) = announce([
    'description'   => 'Check presence of test units for given package and run them, if any.',
    'params'        => [
        'package'   => [
            'description'   =>  "Name of the package on which to perform test units",
            'type'          =>  'string',
            'default'       =>  '*'
        ],
        'test'   => [
            'description'   =>  "ID of the specific test to perform (by default all tests are performed).",
            'type'          =>  'integer',
            'default'       =>  0
        ],
        'set'   => [
            'description'   =>  "ID of the specific test to perform (by default all tests are performed).",
            'type'          =>  'string',
            'default'       =>  null
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

$failed = false;
$tests_path = "packages/{$params['package']}/tests";

if(is_dir($tests_path)) {
    foreach (glob($tests_path."/*.php") as $filename) {
        $set = basename($filename, '.php');
        if($params['set'] && $params['set'] != $set) {
            continue;
        }
        include($filename);
        $tester = new Tester($tests);
        $body = $tester->test($params['test'])->toArray();
        if(isset($body['failed'])) {
            $failed = true;
        }
        $result[$set] = $body;
    }
}

if($params['logs']) {
    $result['logs'] = file_get_contents(QN_LOG_STORAGE_DIR.'/error.log').file_get_contents(QN_LOG_STORAGE_DIR.'/eq_error.log');
}

$providers['context']->httpResponse()
                     ->body($result)
                     ->send();

// if at least one test failed, force non-zero exit code
if($failed) {
    exit(1);
}