<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

$packages = eQual::run('get', 'config_packages');

[$params, $providers] = eQual::announce([
    'description'   => 'Test action and data controller announcements against the controller schema.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Package to validate.',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'level' => [
            'type'          => 'string',
            'description'   => 'Level of notification to limit the output to.',
            'selection'     => ['*', 'warn', 'error'],
            'default'       => '*'
        ],
        'strict' => [
            'type'          => 'boolean',
            'description'   => 'Flag reserved for package consistency interface compatibility.',
            'default'       => false
        ],
        'exit_on_error' => [
            'type'          => 'boolean',
            'description'   => 'Exit with a non-zero status code when consistency errors are found.',
            'default'       => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'constants'     => ['EQ_BASEDIR'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$filter_result = function(array $result, string $level): array {
    foreach($result as $index => $line) {
        if(strpos($line, 'ERROR') === 0 && !in_array($level, ['*', 'error'])) {
            unset($result[$index]);
            continue;
        }
        if(strpos($line, 'WARN') === 0 && !in_array($level, ['*', 'warn'])) {
            unset($result[$index]);
            continue;
        }
    }
    return array_values($result);
};

$count_result = function(array $result): array {
    $counts = ['errors' => 0, 'warnings' => 0];
    foreach($result as $line) {
        if(strpos($line, 'ERROR') === 0) {
            ++$counts['errors'];
        }
        elseif(strpos($line, 'WARN') === 0) {
            ++$counts['warnings'];
        }
    }
    return $counts;
};

$format_schema_errors = function(array $errors): string {
    $formatted = [];
    foreach($errors as $path => $messages) {
        foreach((array) $messages as $message) {
            if(!is_scalar($message)) {
                $message = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            $message = preg_replace('/\s+/', ' ', (string) $message);
            $formatted[] = ($path === '' ? '/' : $path).': '.$message;
        }
    }
    return implode('; ', $formatted);
};

$has_php_files = function(string $directory) use(&$has_php_files): bool {
    if(count(glob($directory.'/*.php'))) {
        return true;
    }
    foreach(glob($directory.'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $subdirectory) {
        if($has_php_files($subdirectory)) {
            return true;
        }
    }
    return false;
};

$list_controllers = function(string $directory, string $parent_name) use(&$list_controllers, $has_php_files): array {
    $controllers = [];
    if(!is_dir($directory)) {
        return $controllers;
    }

    foreach(glob($directory.'/*') as $node) {
        $name = pathinfo($node, PATHINFO_FILENAME);
        if(is_dir($node)) {
            if(!$has_php_files($node)) {
                continue;
            }
            $controllers = array_merge(
                $controllers,
                $list_controllers($node, strlen($parent_name) ? $parent_name.'_'.$name : $name)
            );
        }
        elseif(pathinfo($node, PATHINFO_EXTENSION) === 'php') {
            $controllers[] = (strlen($parent_name) ? $parent_name.'_' : '').$name;
        }
    }

    return $controllers;
};

$load_announcement = function(string $operation, string $controller): ?array {
    $descriptor_spec = [
        ['pipe', 'r'],
        ['pipe', 'w'],
        ['pipe', 'w']
    ];
    $process = proc_open(
        [PHP_BINARY, EQ_BASEDIR.'/run.php', "--{$operation}={$controller}", '--announce=true'],
        $descriptor_spec,
        $pipes,
        EQ_BASEDIR
    );
    if(!is_resource($process)) {
        return null;
    }

    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);

    if($status !== 0 || $errors !== '') {
        return null;
    }

    $response = json_decode($output, true);
    if(
        !is_array($response)
        || !isset($response['announcement'])
        || !is_array($response['announcement'])
    ) {
        return null;
    }

    return $response['announcement'];
};

$result = [];
$package_directory = 'packages/'.$params['package'];
$controllers = [
    'actions' => $list_controllers($package_directory.'/actions', $params['package']),
    'data'    => $list_controllers($package_directory.'/data', $params['package'])
];

foreach(['actions' => 'do', 'data' => 'get'] as $controller_type => $operation) {
    $label = $controller_type === 'actions' ? 'Action' : 'Data provider';
    foreach(($controllers[$controller_type] ?? []) as $controller) {
        $announcement = $load_announcement($operation, $controller);
        if(is_null($announcement)) {
            $result[] = "ERROR - CTL - {$label} {$controller}: missing announcement metadata.";
            continue;
        }

        try {
            $validation = eQual::run('get', 'json-validate', [
                'json'      => json_encode(
                    $announcement,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ),
                'schema_id' => 'urn:equal:json-schema:core:controller.action'
            ]);
        }
        catch(Exception $e) {
            $result[] = "ERROR - CTL - {$label} {$controller}: unable to validate announcement metadata.";
            continue;
        }

        if(!is_array($validation) || !($validation['result'] ?? false)) {
            $details = is_array($validation) && isset($validation['errors']) && is_array($validation['errors'])
                ? $format_schema_errors($validation['errors'])
                : 'unknown schema validation error';
            $result[] = "ERROR - CTL - {$label} {$controller}: invalid announcement schema ({$details}).";
        }
    }
}

$counts = $count_result($result);
$result = $filter_result($result, $params['level']);

if(!count($result)) {
    $result[] = 'INFO - Nothing to report.';
}

$context
    ->httpResponse()
    ->body([
        'result'   => $result,
        'errors'   => $counts['errors'],
        'warnings' => $counts['warnings']
    ])
    ->send();

if($counts['errors'] && $params['exit_on_error']) {
    exit(1);
}
