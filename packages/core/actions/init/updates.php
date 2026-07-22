<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Execute pending package update scripts from packages/{package}/updates.',
    'params'        => [
        'package' => [
            'description'   => 'Package whose pending update scripts must be executed.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'constants'     => ['L10N_TIMEZONE'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$packages = eQual::run('get', 'config_packages');

if(!in_array($params['package'], $packages, true)) {
    throw new Exception('unknown_package', EQ_ERROR_INVALID_PARAM);
}

$package = $params['package'];
$packages_log_file = EQ_BASEDIR . '/log/packages.json';
$package_first_init_at = null;

if(file_exists($packages_log_file)) {
    $json = file_get_contents($packages_log_file);
    if($json === false) {
        throw new Exception('packages_log_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    $map_packages = json_decode($json, true);
    if(!is_array($map_packages)) {
        throw new Exception('invalid_packages_log', EQ_ERROR_INVALID_CONFIG);
    }

    if(isset($map_packages[$package])) {
        if(empty($map_packages[$package]['first'])) {
            throw new Exception('invalid_packages_log', EQ_ERROR_INVALID_CONFIG);
        }

        try {
            $package_first_init_at = new DateTimeImmutable($map_packages[$package]['first']);
        }
        catch(Exception $e) {
            throw new Exception('invalid_packages_log', EQ_ERROR_INVALID_CONFIG);
        }
    }
}

$updates_folder = EQ_BASEDIR . "/packages/$package/updates";
$updates_log_file = EQ_BASEDIR . '/log/updates.json';
$packages_log_file = EQ_BASEDIR . '/log/packages.json';
$map_updates = [];

if(file_exists($updates_log_file)) {
    $json = file_get_contents($updates_log_file);

    if($json === false) {
        throw new Exception('updates_log_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    $map_updates = json_decode($json, true);

    if(!is_array($map_updates)) {
        throw new Exception('invalid_updates_log', EQ_ERROR_INVALID_CONFIG);
    }
}

if(!isset($map_updates[$package]) || !is_array($map_updates[$package])) {
    $map_updates[$package] = [];
}

$package_initialized_timestamp = null;

if(file_exists($packages_log_file)) {
    $json = file_get_contents($packages_log_file);

    if($json === false) {
        throw new Exception('packages_log_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    $map_packages = json_decode($json, true);

    if(!is_array($map_packages)) {
        throw new Exception('invalid_packages_log', EQ_ERROR_INVALID_CONFIG);
    }

    if(isset($map_packages[$package])) {
        if(!is_string($map_packages[$package])) {
            throw new Exception('invalid_package_init_date', EQ_ERROR_INVALID_CONFIG);
        }

        if(!preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/', $map_packages[$package], $matches)) {
            throw new Exception('invalid_package_init_date', EQ_ERROR_INVALID_CONFIG);
        }

        $package_initialized_timestamp = $matches[1] . $matches[2] . $matches[3] . $matches[4] . $matches[5] . $matches[6];
    }
}

$update_scripts = [];

if(is_dir($updates_folder)) {
    foreach(glob($updates_folder . '/*.php') as $filename) {
        $script = basename($filename);
        $script_name = pathinfo($script, PATHINFO_FILENAME);
        $script_parts = preg_split('/[_-]/', $script_name, 2);
        $timestamp = $script_parts[0] ?? '';

        if(!preg_match('/^\d{14}$/', $timestamp)) {
            continue;
        }

        if($package_initialized_timestamp === null || $timestamp <= $package_initialized_timestamp) {
            continue;
        }

        $update_scripts[$script] = $filename;
    }
}

$executed = [];
$skipped = [];

foreach($update_scripts as $script => $filename) {
    if(isset($map_updates[$package][$script])) {
        $skipped[] = $script;
        continue;
    }

    if($package_first_init_at !== null) {
        $update_script_created_after_init = false;
        if(preg_match('/^(\d{14})_/', $script, $matches) === 1) {
            $script_created_at = DateTimeImmutable::createFromFormat(
                '!YmdHis',
                $matches[1],
                new DateTimeZone(constant('L10N_TIMEZONE'))
            );

            if($script_created_at !== false && $package_first_init_at <= $script_created_at) {
                $update_script_created_after_init = true;
            }
        }

        if(!$update_script_created_after_init) {
            $skipped[] = $script;
            continue;
        }
    }

    $updates_log_folder = dirname($updates_log_file);

    if(!is_dir($updates_log_folder) || !is_writable($updates_log_folder)) {
        throw new Exception('log_dir_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    if(file_exists($updates_log_file) && !is_writable($updates_log_file)) {
        throw new Exception('log_file_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    include_once $filename;

    $map_updates[$package][$script] = date('c');

    $json = json_encode($map_updates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if($json === false || file_put_contents($updates_log_file, $json, LOCK_EX) === false) {
        throw new Exception('updates_log_not_accessible', EQ_ERROR_INVALID_CONFIG);
    }

    $executed[] = $script;
}

$context
    ->httpResponse()
    ->body([
        'package'  => $package,
        'executed' => $executed,
        'skipped'  => $skipped
    ])
    ->send();
