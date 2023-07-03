<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnection;
use equal\fs\FSManipulator as FS;
use equal\orm\Field;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

list($params, $providers) = announce([
    'description'   => 'Initialize database for given package. If no package is given, initialize core package.',
    'params'        => [
        'package' => [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string',
            'in'            => array_values($packages),
            'default'       => 'core'
        ],
        'cascade' => [
            'description'   => 'Cascade initialization of the packages marked as dependencies.',
            'type'          => 'boolean',
            'default'       => true
        ],
        'import' => [
            'description'   => 'Request for importing initial data.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'import_cascade' => [
            'description'   => 'Import initial data for dependencies as well.',
            'type'          => 'boolean',
            'default'       => true
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'providers'     => ['context', 'orm', 'adapt'],
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $orm, $dap) = [$providers['context'], $providers['orm'], $providers['adapt']];

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');

$json = run('do', 'test_db-access');
if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}
// retrieve connection object
$db = DBConnection::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();

if(!$db) {
    throw new Exception('missing_database', QN_ERROR_INVALID_CONFIG);
}

$db_class = get_class($db);

// 1) Check in manifest.json for prerequisite initialization

if(!isset($GLOBALS['QN_INIT_DEPENDENCIES'])) {
    $GLOBALS['QN_INIT_DEPENDENCIES'] = [];
}

// mark current package as being initialized (to prevent recursion)
$GLOBALS['QN_INIT_DEPENDENCIES'][$params['package']] = true;

// retrieve manifest of target package, if any
$package_manifest = [];
if(file_exists("packages/{$params['package']}/manifest.json")) {
    $package_manifest = json_decode(file_get_contents("packages/{$params['package']}/manifest.json"), true);
    if(!$package_manifest) {
        throw new Exception("Invalid manifest for package {$params['package']}: ".json_last_error_msg().'.', QN_ERROR_UNKNOWN);
    }
}

// check if there are dependencies
if($params['cascade'] && isset($package_manifest['depends_on']) && is_array($package_manifest['depends_on'])) {
    // initiate dependency packages that are not yet processed, if requested
    foreach($package_manifest['depends_on'] as $dependency) {
        if(!isset($GLOBALS['QN_INIT_DEPENDENCIES'][$dependency])) {
            try {
                // init package of sub packages (perform as root script)
                eQual::run('do', 'init_package', [
                        'package'           => $dependency,
                        'cascade'           => $params['cascade'],
                        'import'            => $params['import'] && $params['import_cascade']
                    ],
                    true);
            }
            catch(Exception $e) {
                if($e->getCode()) {
                    throw new Exception("Unable to initialize dependency package {$dependency}: ".$e->getMessage(), QN_ERROR_UNKNOWN);
                }
            }
        }
    }
}

// 1) Init DB with SQL schema

/*  start-tables_init */

// retrieve schema for given package
$data = eQual::run('get', 'utils_sql-schema', ['package' => $params['package'], 'full' => false]);

// push each line/query into an array
$queries = explode(";", $data['result']);

// send first batch of queries to the DBMS
foreach($queries as $query) {
    $db->sendQuery($query);
}

/*  end-tables_init */

// #todo : make distinction between mandatory initial data and demo data

// 2) Populate tables with predefined data
$data_folder = "packages/{$params['package']}/init/data";
if($params['import'] && file_exists($data_folder) && is_dir($data_folder)) {
    // handle JSON files
    foreach (glob($data_folder."/*.json") as $json_file) {
        $data = file_get_contents($json_file);
        $classes = json_decode($data, true);
        foreach($classes as $class){
            $entity = $class['name'];
            $lang = $class['lang'];
            $model = $orm->getModel($entity);
            $schema = $model->getSchema();

            $objects_ids = [];

            foreach($class['data'] as $odata) {
                foreach($odata as $field => $value) {
                    $f = new Field($schema[$field]);
                    $odata[$field] = $adapter->adaptIn($value, $f->getUsage());
                }
                if(isset($odata['id'])) {
                    $res = $orm->search($entity, ['id', '=', $odata['id']]);
                    if($res > 0 && count($res)) {
                        // object already exist, but either values or language might differ
                        $id = $odata['id'];
                        $res = $orm->update($entity, $id, $odata, $lang);
                        $objects_ids[] = $id;
                    }
                    else {
                        $objects_ids[] = $orm->create($entity, $odata, $lang);
                    }
                }
                else {
                    $objects_ids[] = $orm->create($entity, $odata, $lang);
                }
            }

            // force a first generation of computed fields, if any
            $computed_fields = [];
            foreach($schema as $field => $def) {
                if($def['type'] == 'computed') {
                    $computed_fields[] = $field;
                }
            }
            $orm->read($entity, $objects_ids, $computed_fields, $lang);
        }
    }
}

// 3) If a `bin` folder exists, copy its content to /bin/<package>/
$bin_folder = "packages/{$params['package']}/init/bin";
if($params['import'] && file_exists($bin_folder) && is_dir($bin_folder)) {
    exec("cp -r $bin_folder bin/{$params['package']}");
    exec("chown www-data:www-data -R bin/{$params['package']}");
}

// 4) If a `routes` folder exists, copy its content to /config/routing/
$route_folder = "packages/{$params['package']}/init/routes";
if(file_exists($route_folder) && is_dir($route_folder)) {
    exec("cp -r $route_folder/* config/routing");
}

// 5) Export the compiled apps to related public folders
if(isset($package_manifest['apps']) && is_array($package_manifest['apps'])) {

    foreach($package_manifest['apps'] as $app) {

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
                throw new Exception("Invalid manifest for app {$app}: ".json_last_error_msg().'.', QN_ERROR_UNKNOWN);
            }
        }

        // checks wether the folder exists or not
        if(file_exists("public/$app")) {
            // remove existing folder, if present
            if(FS::removeDir("public/$app")) {
                // trigger_error("PHP::error removing folder : $message", QN_REPORT_DEBUG);
                // throw new Exception('fs_removing_file_failure', QN_ERROR_UNKNOWN);
            }
        }

        // (re)create the (empty) folder
        if(!mkdir("public/$app")) {
            // trigger_error("PHP::error moving file : $message", QN_REPORT_DEBUG);
            // throw new Exception('fs_moving_file_failure', QN_ERROR_UNKNOWN);
        }

        // verify the checksum
        $version_md5 = md5_file("$app_path/web.app");
        if(isset($app_manifest['checksum'])) {
            if($version_md5 != $app_manifest['checksum']) {
                // #todo - not required for now, nice to have: would increase version identification
                // throw new Exception("Invalid checksum for app {$app}: ".json_last_error_msg().'.', QN_ERROR_UNKNOWN);
            }
        }

        // extract the app archive
        $zip = new ZipArchive;
        if (!$zip->open("$app_path/web.app")) {
            throw new Exception("Unable to export app {$app}: ".json_last_error_msg().'.', QN_ERROR_UNKNOWN);
        }
        // export to public folder
        $zip->extractTo("public/$app/");
        $zip->close();

        // add a version file (holding md5 of the web.app archive)
        if(file_exists("public/$app")) {
            file_put_contents("public/$app/version", $version_md5);
        }
    }
}

// mark the package as initialized (installed)

/**
 * The list of installed package is maintained in the file /log/packages.json,
 * as an associative array mapping package names with the latest moment they were initialized
 */
$packages = [];

if(file_exists("log/packages.json")) {
    $json = file_get_contents("log/packages.json");
    $packages = json_decode($json, true);
}

$packages[$params['package']] = date('c');
file_put_contents("log/packages.json", json_encode($packages, JSON_PRETTY_PRINT));

$context->httpResponse()
        ->status(201)
        ->send();
