<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description' => 'Install an external binary dependency and persist its runtime files in a dedicated bundle.',
    'help'        => "The binary and its shared libraries are persisted under `/opt/bin/.equal-packages/{binary}`.
                `configuration.persist.paths` copies required absolute files or directories to the same path under `/opt/bin/.equal-packages/{binary}/rootfs`.
                `configuration.persist.environment` maps environment variables to absolute paths to replicate under `/opt/bin/.equal-packages/{binary}/rootfs`.",
    'params'      => [
        'binary' => [
            'description' => 'Name of the binary dependency.',
            'type'        => 'string',
            'required'    => true
        ],
        'configuration' => [
            'description' => 'Binary dependency configuration from the package manifest.',
            'type'        => 'array',
            'required'    => true
        ]
    ],
    'access'      => [
        'visibility' => 'private'
    ],
    'providers'   => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$runCommand = function(string $command, ?string $path = null): array {
    if($path !== null) {
        $command = 'export PATH=' . escapeshellarg($path) . '; ' . $command;
    }

    $output = [];
    $result_code = 0;
    exec($command . ' 2>&1', $output, $result_code);

    return [$result_code, $output];
};

$ensureDirectory = function(string $directory) use($runCommand): void {
    [$result_code] = $runCommand('mkdir -p -- ' . escapeshellarg($directory));
    if($result_code !== 0 || !is_dir($directory)) {
        throw new Exception('unable_to_create_persist_directory', EQ_ERROR_UNKNOWN);
    }
};

$findBinary = function(string $binary, string $path) use($runCommand): ?string {
    [$result_code, $output] = $runCommand('command -v ' . escapeshellarg($binary), $path);
    $binary_path = trim($output[0] ?? '');
    if($result_code !== 0 || !is_executable($binary_path)) {
        return null;
    }
    return realpath($binary_path) ?: $binary_path;
};

$getSharedLibraries = function(string $binary_path) use($runCommand): array {
    $libraries = [];
    [, $output] = $runCommand('ldd ' . escapeshellarg($binary_path) . ' 2>/dev/null');
    foreach($output as $line) {
        if(!preg_match('/=>\s+(\/\S+)/', $line, $matches)) {
            continue;
        }

        $filename = basename($matches[1]);
        // #memo - System runtime libraries are provided by the container base image
        if(preg_match('/^lib(?:c|dl|m|pthread|resolv|rt|util)\.so(?:\.|$)/', $filename)) {
            continue;
        }

        $libraries[] = $matches[1];
    }

    return array_values(array_unique($libraries));
};

$copyPath = function(string $source, string $destination) use($ensureDirectory, $runCommand): void {
    if(!file_exists($source) && !is_link($source)) {
        throw new Exception('persist_source_not_found', EQ_ERROR_UNKNOWN);
    }

    $ensureDirectory(dirname($destination));

    if(is_dir($source)) {
        $ensureDirectory($destination);
        $source = rtrim($source, '/') . '/.';
    }

    [$result_code] = $runCommand(
        'cp -aL -- ' . escapeshellarg($source) . ' ' . escapeshellarg($destination)
    );

    if($result_code !== 0) {
        throw new Exception('unable_to_persist_file', EQ_ERROR_UNKNOWN);
    }
};

$copyRuntime = function(string $binary_path, array $libraries, string $bin_dir, ?string $lib_dir = null) use($copyPath): void {
    $copyPath($binary_path, $bin_dir . '/' . basename($binary_path));
    foreach($libraries as $library) {
        $copyPath($library, ($lib_dir ?? $bin_dir) . '/' . basename($library));
    }
};

$resolveEnvironmentValue = function($value, string $staging_bundle, string $final_bundle): string {
    $values = is_array($value) ? $value : [$value];
    $resolved_values = [];

    foreach($values as $configured_value) {
        if(!is_string($configured_value) || strlen($configured_value) === 0 || $configured_value[0] !== '/') {
            throw new Exception('invalid_persist_environment_value', EQ_ERROR_INVALID_CONFIG);
        }

        $staging_value = $staging_bundle . '/rootfs/' . ltrim($configured_value, '/');
        $is_pattern = strpbrk($staging_value, '*?[') !== false;
        $matches = $is_pattern
            ? (glob($staging_value) ?: [])
            : ((file_exists($staging_value) || is_link($staging_value)) ? [$staging_value] : []);
        if(empty($matches)) {
            throw new Exception('persist_environment_path_not_found', EQ_ERROR_INVALID_CONFIG);
        }
        sort($matches, SORT_NATURAL);
        foreach($matches as $match) {
            if(strpos($match, $staging_bundle . '/rootfs/') !== 0) {
                throw new Exception('invalid_persist_environment_path', EQ_ERROR_INVALID_CONFIG);
            }
            $resolved_values[] = $final_bundle . substr($match, strlen($staging_bundle));
        }
    }

    return implode(PATH_SEPARATOR, array_values(array_unique($resolved_values)));
};

$binary = $params['binary'];
if(!preg_match('/^[A-Za-z0-9._+-]+$/', $binary)) {
    throw new Exception('invalid_binary_name', EQ_ERROR_INVALID_PARAM);
}

$configuration = $params['configuration'];
$check_cmd = $configuration['check'] ?? "$binary --version";
$opt_bin_dir = '/opt/bin';

// Every binary uses a bundle; `persist` only adds runtime paths and environment variables.
$persist_configuration = $configuration['persist'] ?? [];
if(!is_array($persist_configuration)) {
    throw new Exception('invalid_persist_configuration', EQ_ERROR_INVALID_CONFIG);
}

$wrapper_path = $opt_bin_dir . '/' . $binary;
$package_key = preg_replace('/[^A-Za-z0-9._-]+/', '_', $binary);
$packages_dir = $opt_bin_dir . '/.equal-packages';
$bundle_dir = $packages_dir . '/' . $package_key;

$current_path = getenv('PATH') ?: '';
$system_path = implode(PATH_SEPARATOR, array_filter(
    explode(PATH_SEPARATOR, $current_path),
    function(string $path) use($opt_bin_dir): bool {
        return rtrim($path, '/') !== $opt_bin_dir;
    }
));

[$current_check_code] = $runCommand($check_cmd);
if($current_check_code === 0 && is_executable($wrapper_path) && is_file($bundle_dir . '/.ready')) {
    $context->httpResponse()
            ->status(204)
            ->send();
    return;
}

if(file_exists($bundle_dir) || is_link($bundle_dir)) {
    throw new Exception('persist_bundle_requires_cleanup', EQ_ERROR_UNKNOWN);
}

// Ignore a stale persistent executable while checking the package installation.
[$system_check_code] = $runCommand($check_cmd, $system_path);
if($system_check_code !== 0) {
    if(strtolower(PHP_OS_FAMILY) !== 'linux') {
        throw new Exception('unsupported_os_for_binary', EQ_ERROR_UNKNOWN);
    }

    $os_release = file_exists('/etc/os-release') ? (parse_ini_file('/etc/os-release') ?: []) : [];
    $linux_flavour = strtolower($os_release['ID'] ?? '');
    $packages = is_array($configuration['package'] ?? null) ? $configuration['package'] : [];
    $binary_package_name = $packages[$linux_flavour] ?? $packages['generic'] ?? $binary;

    $installers = [
        'apt-get' => 'apt-get update && apt-get install -y ',
        'dnf'     => 'dnf install -y ',
        'yum'     => 'yum install -y '
    ];
    $install_cmd = null;
    foreach($installers as $manager => $command) {
        [$manager_code, $manager_output] = $runCommand('command -v ' . $manager, $system_path);
        trigger_error("PHP::Installing binary {$binary}: command exited with code {$manager_code}: command -v {$manager}\n" . (empty($manager_output) ? '[no output]' : implode("\n", $manager_output)), $manager_code === 0 ? EQ_REPORT_INFO : EQ_REPORT_WARNING);
        if($manager_code === 0) {
            $install_cmd = $command . escapeshellarg($binary_package_name);
            break;
        }
    }
    if($install_cmd === null) {
        throw new Exception('unsupported_linux_package_manager', EQ_ERROR_UNKNOWN);
    }

    [$install_code, $install_output] = $runCommand($install_cmd, $system_path);
    trigger_error("PHP::Installing binary {$binary}: command exited with code {$install_code}: {$install_cmd}\n" . (empty($install_output) ? '[no output]' : implode("\n", $install_output)), $install_code === 0 ? EQ_REPORT_INFO : EQ_REPORT_WARNING);
    if($install_code !== 0) {
        throw new Exception('install_error_for_binary', EQ_ERROR_UNKNOWN);
    }

    [$system_check_code, $system_check_output] = $runCommand($check_cmd, $system_path);
    trigger_error("PHP::Installing binary {$binary}: command exited with code {$system_check_code}: {$check_cmd}\n" . (empty($system_check_output) ? '[no output]' : implode("\n", $system_check_output)), $system_check_code === 0 ? EQ_REPORT_INFO : EQ_REPORT_WARNING);
    if($system_check_code !== 0) {
        throw new Exception('binary_missing_after_install', EQ_ERROR_UNKNOWN);
    }
}

$binary_path = $findBinary($binary, $system_path);
if($binary_path === null) {
    throw new Exception('binary_not_found', EQ_ERROR_UNKNOWN);
}

$libraries = $getSharedLibraries($binary_path);

$staging_id = bin2hex(random_bytes(4));
$staging_bundle = $packages_dir . '/.' . $package_key . '-' . $staging_id . '.tmp';

$ensureDirectory($packages_dir);
$ensureDirectory($staging_bundle . '/bin');
$ensureDirectory($staging_bundle . '/lib');
$ensureDirectory($staging_bundle . '/rootfs');
$copyRuntime($binary_path, $libraries, $staging_bundle . '/bin', $staging_bundle . '/lib');

$configured_paths = $persist_configuration['paths'] ?? [];
if(!is_array($configured_paths)) {
    throw new Exception('invalid_persist_paths', EQ_ERROR_INVALID_CONFIG);
}

foreach($configured_paths as $configured_path) {
    if(!is_string($configured_path) || strlen($configured_path) === 0) {
        throw new Exception('invalid_persist_path', EQ_ERROR_INVALID_CONFIG);
    }
    if($configured_path[0] !== '/') {
        throw new Exception('persist_path_must_be_absolute', EQ_ERROR_INVALID_CONFIG);
    }

    $source_paths = strpbrk($configured_path, '*?[') === false
        ? ((file_exists($configured_path) || is_link($configured_path)) ? [$configured_path] : [])
        : (glob($configured_path) ?: []);

    if(empty($source_paths)) {
        throw new Exception('persist_source_not_found', EQ_ERROR_INVALID_CONFIG);
    }

    foreach($source_paths as $source_path) {
        $source_real_path = realpath($source_path) ?: $source_path;
        $normalized_source = rtrim($source_real_path, '/');
        if(strpos($normalized_source . '/', $opt_bin_dir . '/') === 0) {
            throw new Exception('invalid_persist_source', EQ_ERROR_INVALID_CONFIG);
        }

        $relative_path = ltrim($source_path, '/');
        $destination = $staging_bundle . '/rootfs/' . $relative_path;
        $copyPath($source_path, $destination);
    }
}

$configured_environment = $persist_configuration['environment'] ?? [];
if(!is_array($configured_environment)) {
    throw new Exception('invalid_persist_environment', EQ_ERROR_INVALID_CONFIG);
}

$environment_lines = [
    'export PATH=' . escapeshellarg($bundle_dir . '/bin') . '${PATH:+:' . '$PATH}',
    'export LD_LIBRARY_PATH=' . escapeshellarg($bundle_dir . '/lib') . '${LD_LIBRARY_PATH:+:' . '$LD_LIBRARY_PATH}'
];

foreach($configured_environment as $name => $value) {
    if(!is_string($name) || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
        throw new Exception('invalid_persist_environment_name', EQ_ERROR_INVALID_CONFIG);
    }
    $resolved_value = $resolveEnvironmentValue($value, $staging_bundle, $bundle_dir);
    $environment_lines[] = 'export ' . $name . '=' . escapeshellarg($resolved_value) . '${' . $name . ':+:' . '$' . $name . '}';
}

if(file_put_contents($staging_bundle . '/environment.sh', implode("\n", $environment_lines) . "\n") === false) {
    throw new Exception('unable_to_write_persist_environment', EQ_ERROR_UNKNOWN);
}

if(!rename($staging_bundle, $bundle_dir)) {
    throw new Exception('unable_to_activate_persist_bundle', EQ_ERROR_UNKNOWN);
}

$wrapper_temporary_path = $opt_bin_dir . '/.' . $package_key . '-' . $staging_id . '.tmp';
$wrapper_content = implode("\n", [
    '#!/bin/sh',
    '# Generated by init_package_binary.',
    '. ' . escapeshellarg($bundle_dir . '/environment.sh'),
    'exec ' . escapeshellarg($bundle_dir . '/bin/' . basename($binary_path)) . ' "$@"'
]) . "\n";

if(file_put_contents($wrapper_temporary_path, $wrapper_content) === false) {
    throw new Exception('unable_to_write_persist_wrapper', EQ_ERROR_UNKNOWN);
}

chmod($wrapper_temporary_path, 0755);

if(is_dir($wrapper_path)) {
    throw new Exception('invalid_persist_wrapper_path', EQ_ERROR_INVALID_CONFIG);
}
if(!rename($wrapper_temporary_path, $wrapper_path)) {
    throw new Exception('unable_to_activate_persist_wrapper', EQ_ERROR_UNKNOWN);
}

$persistent_path = $opt_bin_dir . (strlen($system_path) > 0 ? PATH_SEPARATOR . $system_path : '');
[$persistent_check_code] = $runCommand($check_cmd, $persistent_path);
if($persistent_check_code !== 0) {
    @unlink($wrapper_path);
    throw new Exception('persisted_binary_check_failed', EQ_ERROR_UNKNOWN);
}

touch($bundle_dir . '/.ready');

$context->httpResponse()
        ->status(204)
        ->send();
