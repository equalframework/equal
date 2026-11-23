<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\test\Tester;

[$params, $providers] = eQual::announce([
    'description'   => 'Check presence of test units for given package and run them, if any.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'usage'         => 'orm/package',
            'description'   => "Name of the package on which to perform test units",
            'default'       => '*'
        ],
        'test' => [
            'type'          => 'integer',
            'description'   => "ID of the specific test to perform (by default all tests are performed).",
            'default'       => 0
        ],
        'set' => [
            'type'          => 'string',
            'description'   => "ID of the specific test to perform (by default all tests are performed).",
            'default'       => null
        ],
        'logs' => [
            'type'          => 'boolean',
            'description'   => "Embed logs in result",
            'default'       => false
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$result = [];

$failed = false;
$tests_path = "packages/{$params['package']}/tests";

if(is_dir($tests_path)) {
    foreach (glob($tests_path."/*.php") as $filename) {
        $set = basename($filename, '.php');
        if(isset($params['set']) && $params['set'] != $set) {
            continue;
        }
        $tests = [];
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
    $result['logs'] = file_get_contents(EQ_LOG_STORAGE_DIR.'/equal.log');
}

$context
    ->httpResponse()
    ->body($result)
    ->send();

// if at least one test failed, force non-zero exit code
if($failed) {
    exit(1);
}