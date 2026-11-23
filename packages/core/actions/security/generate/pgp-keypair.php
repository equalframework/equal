<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\fs\FSManipulator;

[$params, $providers] = eQual::announce([
    'description'   => 'Generates an ASCII-armored OpenPGP key pair and its passphrase.',
    'params'        => [
        'name' => [
            'type'          => 'string',
            'description'   => 'Name associated with the OpenPGP identity.',
            'required'      => true
        ],
        'email' => [
            'type'          => 'string',
            'usage'         => 'email',
            'description'   => 'Email address associated with the OpenPGP identity.',
            'help'          => 'This is the `recipient` used by OpenPGP to identify the public key to use for decryption.',
            'required'      => true
        ],
        'expiry_date' => [
            'type'          => 'string',
            'description'   => 'Key expiry accepted by GnuPG, such as 0, 1y, or 2028-12-31.',
            'default'       => '0'
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
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

$command_error = null;

$runGpg = function(array $arguments, bool $throw_on_error = true) use(&$command_error): array {
    $command = implode(' ', array_map('escapeshellarg', array_merge(['gpg'], $arguments)));
    $output = [];
    $result_code = 0;

    exec($command . ' 2>&1', $output, $result_code);

    if($result_code !== 0) {
        $diagnostic = "APP::GnuPG command failed with code {$result_code}.\n" . implode("\n", $output);
        if($throw_on_error) {
            $command_error = $diagnostic;
            throw new Exception('pgp_key_generation_failed', EQ_ERROR_UNKNOWN);
        }
        trigger_error($diagnostic, EQ_REPORT_WARNING);
    }

    return [$result_code, $output];
};

$name = trim($params['name']);
$email = trim($params['email']);
$expiry_date = trim($params['expiry_date']);

if($name === '' || strpbrk($name, "\r\n\0") !== false) {
    throw new Exception('invalid_pgp_name', EQ_ERROR_INVALID_PARAM);
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strpbrk($email, "\r\n\0") !== false) {
    throw new Exception('invalid_pgp_email', EQ_ERROR_INVALID_PARAM);
}
if(!preg_match('/^(?:0|[1-9][0-9]*[dwmy]?|[0-9]{4}-[0-9]{2}-[0-9]{2})$/', $expiry_date)) {
    throw new Exception('invalid_pgp_expiry_date', EQ_ERROR_INVALID_PARAM);
}

$temporary_dir = null;
$keyring_dir = null;
$config_file = null;
$private_key_file = null;
$public_key_file = null;
$fingerprint = null;

try {
    $passphrase = bin2hex(random_bytes(32));
    $temporary_dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'equal-pgp-'
        . bin2hex(random_bytes(16));
    $keyring_dir = $temporary_dir . DIRECTORY_SEPARATOR . 'keyring';
    $config_file = $temporary_dir . DIRECTORY_SEPARATOR . 'key-gen.conf';
    $private_key_file = $temporary_dir . DIRECTORY_SEPARATOR . 'gpg-private-key.pgp';
    $public_key_file = $temporary_dir . DIRECTORY_SEPARATOR . 'gpg-public-key.asc';

    if(!mkdir($keyring_dir, 0700, true)) {
        throw new Exception('unable_to_create_pgp_keyring', EQ_ERROR_UNKNOWN);
    }

    $configuration = implode("\n", [
        'Key-Type: RSA',
        'Key-Length: 4096',
        'Key-Usage: sign',
        'Subkey-Type: RSA',
        'Subkey-Length: 4096',
        'Subkey-Usage: encrypt',
        "Name-Real: {$name}",
        "Name-Email: {$email}",
        "Expire-Date: {$expiry_date}",
        "Passphrase: {$passphrase}",
        '%commit',
        ''
    ]);

    if(file_put_contents($config_file, $configuration, LOCK_EX) === false) {
        throw new Exception('unable_to_write_pgp_configuration', EQ_ERROR_UNKNOWN);
    }
    chmod($config_file, 0600);

    $gpg_home_arguments = ['--homedir', $keyring_dir];

    $runGpg(array_merge(
        $gpg_home_arguments,
        ['--batch', '--pinentry-mode', 'loopback', '--generate-key', $config_file]
    ));

    [, $fingerprint_output] = $runGpg(array_merge(
        $gpg_home_arguments,
        ['--batch', '--with-colons', '--fingerprint', '--list-secret-keys', $email]
    ));

    foreach($fingerprint_output as $line) {
        $fields = explode(':', $line);
        if(($fields[0] ?? '') === 'fpr' && !empty($fields[9])) {
            $fingerprint = $fields[9];
            break;
        }
    }

    if($fingerprint === null) {
        throw new Exception('pgp_fingerprint_not_found', EQ_ERROR_UNKNOWN);
    }

    $runGpg(array_merge(
        $gpg_home_arguments,
        [
            '--batch',
            '--pinentry-mode',
            'loopback',
            '--yes',
            '--passphrase',
            $passphrase,
            '--output',
            $private_key_file,
            '--armor',
            '--export-secret-keys',
            $fingerprint
        ]
    ));

    $runGpg(array_merge(
        $gpg_home_arguments,
        [
            '--batch',
            '--yes',
            '--output',
            $public_key_file,
            '--armor',
            '--export',
            $fingerprint
        ]
    ));

    $private_key = file_get_contents($private_key_file);
    $public_key = file_get_contents($public_key_file);
    if($private_key === false || $private_key === '' || $public_key === false || $public_key === '') {
        throw new Exception('unable_to_read_generated_pgp_keys', EQ_ERROR_UNKNOWN);
    }
}
catch(\Throwable $throwable) {
    trigger_error($command_error ?? sprintf(
            'APP::PGP key pair generation failed: %s: %s in %s:%d.',
            get_class($throwable),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine()
        ),
        EQ_REPORT_ERROR
    );
    throw new Exception('pgp_key_generation_failed', EQ_ERROR_UNKNOWN);
}
finally {
    try {
        // remove all temporary files
        if($fingerprint !== null && $keyring_dir !== null && is_dir($keyring_dir)) {
            $gpg_home_arguments = ['--homedir', $keyring_dir];
            $runGpg(
                array_merge($gpg_home_arguments, ['--batch', '--yes', '--delete-secret-keys', $fingerprint]),
                false
            );
            $runGpg(
                array_merge($gpg_home_arguments, ['--batch', '--yes', '--delete-keys', $fingerprint]),
                false
            );
        }

        foreach([$config_file, $private_key_file, $public_key_file] as $file) {
            if($file !== null && is_file($file)) {
                @unlink($file);
            }
        }

        if($temporary_dir !== null && is_dir($temporary_dir) && !FSManipulator::removeDir($temporary_dir)) {
            throw new RuntimeException('unable_to_remove_temporary_pgp_files');
        }
    }
    catch(\Throwable $throwable) {
        trigger_error(
            sprintf(
                'APP::PGP temporary files cleanup failed: %s: %s in %s:%d.',
                get_class($throwable),
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine()
            ),
            EQ_REPORT_ERROR
        );
        throw new Exception('pgp_key_generation_failed', EQ_ERROR_UNKNOWN);
    }
}

$result = [
    'public_key'  => $public_key,
    'private_key' => $private_key,
    'passphrase'  => $passphrase
];

$context
    ->httpResponse()
    ->status(201)
    ->body($result)
    ->send();
