<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'   =>  "This script tests the given package and returns a report about found errors (if any).",
    'params'        =>  [
        'package' =>  [
            'description'   => 'Package to validate.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'level' =>  [
            'description'   => 'Level of notification to limit the output to.',
            'type'          => 'string',
            'selection'     => ['*', 'warn', 'error'],
            'default'       => '*'
        ],
        'strict' =>  [
            'description'   => 'Flag to enable strict mode.',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
$context = $providers['context'];

/*
    #todo :

    * check config and json files syntax.

    * translation files constraints:
        * model helpers : max 45 chars
        * error messages length : max 45 chars

    * for each view, check that each field is present 0 or 1 time
    * for each view, check that the id match an entry in the translation file
    * for each class, check that all fields are each field is present at least in one view

    * check for potential versions conflicts across packages in manifest `requires` (composer dependencies)


    * check consistency of import files (JSON)
        if($entity === 'core\setting\SettingValue') => setting_id should not be present and name should be set.

*/

$controllers = [
    'test_package_classes-consistency',
    'test_package_views-consistency',
    'test_package_i18n-consistency',
    'test_package_db-consistency'
];

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];
$errors_count = 0;
$warnings_count = 0;

foreach($controllers as $controller) {
    try {
        $report = eQual::run('do', $controller, [
            'package'       => $params['package'],
            'level'         => $params['level'],
            'strict'        => $params['strict'],
            'exit_on_error' => false
        ]);
    }
    catch(Exception $e) {
        $message = $e->getMessage();
        $result[] = (strpos($message, 'FATAL') === 0) ? $message : "ERROR - {$controller}: {$message}";
        ++$errors_count;
        continue;
    }

    $errors_count += (int) ($report['errors'] ?? 0);
    $warnings_count += (int) ($report['warnings'] ?? 0);

    foreach(($report['result'] ?? []) as $line) {
        if($line === 'INFO - Nothing to report.') {
            continue;
        }
        $result[] = $line;
    }
}

if(!count($result)) {
    $result[] = 'INFO - Nothing to report.';
}

// send json result
$context->httpResponse()
        ->body([
            'result'   => $result,
            'errors'   => $errors_count,
            'warnings' => $warnings_count
        ])
        ->send();

// exit with an error code according to the requested notification level
if($errors_count || ($warnings_count && in_array($params['level'], ['*', 'warn']))) {
    exit(1);
}
