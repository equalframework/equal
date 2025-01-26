<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnector;
use equal\fs\FSManipulator as FS;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'   => 'Install a specific App from a given package by exporting the related archive to the `/public` folder.',
    'params'        => [
        'package' => [
            'description'   => 'Package holding the App to be initialized.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ],
        'app' => [
            'description'   => 'App to be initialized (installed).',
            'type'          => 'string',
            'required'      => true
        ],
        'force' => [
            'description'   => 'Force initialization even if target folder is already present in `/public`.',
            'type'          => 'boolean',
            'default'       => false
        ]

    ],
    'providers'     => ['context'],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ]
]);

/**
 * @var \equal\php\Context          $context
 */
['context' => $context] = $providers;


if(!class_exists('ZipArchive')) {
    throw new Exception('zip_extension_not_loaded', EQ_ERROR_UNKNOWN);
}

$app = $params['app'];

// path of the app
$app_path = "packages/{$params['package']}/apps/$app";

if(!file_exists($app_path) || !file_exists($app_path.'/web.app')) {
    // ignore apps not relating to an actual folder inside {package}/apps
    continue;
}

$app_manifest = [];

if(file_exists("$app_path/manifest.json")) {
    $app_manifest = json_decode(file_get_contents("$app_path/manifest.json"), true);
    if(!$package_manifest) {
        throw new Exception("Invalid manifest for app {$app}: ".json_last_error_msg().'.', EQ_ERROR_UNKNOWN);
    }
}

// checks wether the folder exists or not
if(file_exists("public/$app")) {

    if(!$params['force']) {
        // ignore existing apps unless forced
        throw new Exception('existing_target_directory', EQ_ERROR_INVALID_PARAM);
    }

    // remove existing folder, if present
    if(FS::removeDir("public/$app")) {
        throw new Exception('fs_removing_file_failure', EQ_ERROR_UNKNOWN);
    }
}

// (re)create the (empty) folder
if(!mkdir("public/$app")) {
    throw new Exception('fs_moving_file_failure', EQ_ERROR_UNKNOWN);
}

// verify the checksum
$version_md5 = md5_file("$app_path/web.app");
if(isset($app_manifest['checksum'])) {
    if($version_md5 != $app_manifest['checksum']) {
        // #todo - not required for now, nice to have: would increase version identification
        // throw new Exception("Invalid checksum for app {$app}: ".json_last_error_msg().'.', EQ_ERROR_UNKNOWN);
    }
}

// extract the app archive
$zip = new ZipArchive;
if (!$zip->open("$app_path/web.app")) {
    throw new Exception("Unable to export app {$app}: ".json_last_error_msg().'.', EQ_ERROR_UNKNOWN);
}

// export to public folder
$zip->extractTo("public/$app/");
$zip->close();

// add a version file (holding md5 of the web.app archive)
if(file_exists("public/$app")) {
    file_put_contents("public/$app/version", $version_md5);
}

$context->httpResponse()
        ->status(201)
        ->send();
