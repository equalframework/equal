<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnector;
use equal\fs\FSManipulator as FS;
use equal\orm\Field;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'   => 'Initialize a package by populating the database with tables mapping the entities defined in the given package and its dependencies.',
    'params'        => [
        'package' => [
            'description'   => 'Package that must be initialized.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ],
        'cascade' => [
            'description'   => 'Request cascade initialization of the dependencies.',
            'type'          => 'boolean',
            'default'       => true
        ],
        'force' => [
            'description'   => 'Request initialization even if package has already been initialized.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'import' => [
            'description'   => 'Request importing initial data.',
            'type'          => 'boolean',
            'default'       => true
        ],
        'import_cascade' => [
            'description'   => 'Import initial data for dependencies as well.',
            'type'          => 'boolean',
            'default'       => true
        ],
        'demo' => [
            'description'   => 'Request importing demo data.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'test' => [
            'description'   => 'Request importing data intended for testing.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'composer' => [
            'description'   => 'Request initialization of composer dependencies (`/vendor`).',
            'type'          => 'boolean',
            'default'       => true
        ],
        'root' => [
            'description'   => 'Mark the script as top-level or as a sub-call (for recursion).',
            'type'          => 'boolean',
            'default'       => true
        ]
    ],
    'constants'     => ['DEFAULT_LANG', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'providers'     => ['context', 'orm'],
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

/**
 * Methods
 */

$importDataFromFolderJsonFiles = function($data_folder) {
    foreach(glob("$data_folder/*.json") as $json_file) {
        $data = file_get_contents($json_file);
        $classes = json_decode($data, true);
        if(!$classes) {
            continue;
        }
        foreach($classes as $class) {
            $entity = $class['name'] ?? null;
            if(!$entity) {
                continue;
            }
            $lang = $class['lang'] ?? constant('DEFAULT_LANG');

            eQual::run('do', 'core_model_import', [
                'entity'    => $entity,
                'data'      => $class['data'],
                'lang'      => $lang
            ]);
        }
    }
};

/**
 * Action
 */

$skip_package = false;

if(file_exists("log/packages.json")) {
    $json = file_get_contents("log/packages.json");
    $packages = json_decode($json, true);
    if(isset($packages[$params['package']]) && !$params['force']) {
        $skip_package = true;
    }
}

if($skip_package) {
    if($params['root']) {
        throw new Exception('package_already_initialized', EQ_ERROR_CONFLICT_OBJECT);
    }
    // silently ignore package when dependency of another
}
else {
    // make sure DB is available
    eQual::run('do', 'test_db-access');

    // retrieve connection object
    $db = DBConnector::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();

    if(!$db) {
        throw new Exception('missing_database', EQ_ERROR_INVALID_CONFIG);
    }

    // remember initial state of composer.json (if present)
    $map_composer_original = [];

    if($params['root'] && $params['composer']) {
        if(file_exists(EQ_BASEDIR.'/composer.json')) {
            $json = file_get_contents(EQ_BASEDIR.'/composer.json');
            $map_composer_original = json_decode($json, true);
        }
    }

    // 1) Check in manifest.json for prerequisite initialization

    // mark current package as being initialized (to prevent recursion)
    if(!isset($GLOBALS['EQ_INIT_DEPENDENCIES'])) {
        $GLOBALS['EQ_INIT_DEPENDENCIES'] = [];
    }
    $GLOBALS['EQ_INIT_DEPENDENCIES'][$params['package']] = true;

    // retrieve manifest of target package, if any
    $package_manifest = [];
    if(file_exists("packages/{$params['package']}/manifest.json")) {
        $package_manifest = json_decode(file_get_contents("packages/{$params['package']}/manifest.json"), true);
        if(!$package_manifest) {
            throw new Exception("Invalid manifest for package {$params['package']}: ".json_last_error_msg().'.', EQ_ERROR_UNKNOWN);
        }
    }

    // check if there are dependencies (must be initialized beforehand)
    if($params['cascade'] && isset($package_manifest['depends_on']) && is_array($package_manifest['depends_on'])) {
        // initiate dependency packages that are not yet processed, if requested
        foreach($package_manifest['depends_on'] as $dependency) {
            if(!isset($GLOBALS['EQ_INIT_DEPENDENCIES'][$dependency])) {
                try {
                    // init package of sub packages (perform as root script)
                    eQual::run('do', 'init_package', [
                            'package'           => $dependency,
                            'cascade'           => $params['cascade'],
                            'import'            => $params['import'] && $params['import_cascade'],
                            'root'              => false
                        ],
                        true);
                }
                catch(Exception $e) {
                    if($e->getCode()) {
                        throw new Exception("Unable to initialize dependency package {$dependency}: ".$e->getMessage(), EQ_ERROR_UNKNOWN);
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

    // 2) Populate tables with predefined data
    $data_folder = "packages/{$params['package']}/init/data";
    if($params['import'] && file_exists($data_folder) && is_dir($data_folder)) {
        $importDataFromFolderJsonFiles($data_folder);
    }

    // 2 bis) Populate tables with demo data, if requested
    $demo_folder = "packages/{$params['package']}/init/demo";
    if($params['demo'] && file_exists($demo_folder) && is_dir($demo_folder)) {
        $importDataFromFolderJsonFiles($demo_folder);
    }

    // 2 ter) Populate tables with test data (intended for tests), if requested
    $test_folder = "packages/{$params['package']}/init/test";
    if($params['test'] && file_exists($test_folder) && is_dir($test_folder)) {
        $importDataFromFolderJsonFiles($test_folder);
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

    $assets_folder = "packages/{$params['package']}/init/assets";
    if(file_exists($assets_folder) && is_dir($assets_folder)) {
        exec("cp -r $assets_folder/* public/assets/");
    }

    // 5) Export the compiled apps to related public folders
    if(isset($package_manifest['apps']) && is_array($package_manifest['apps'])) {

        foreach($package_manifest['apps'] as $app) {

            // ignore app descriptors (inherited apps are handled in `installed-apps`)
            if(is_array($app)) {
                continue;
            }

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
                // remove existing folder, if present
                if(FS::removeDir("public/$app")) {
                    // trigger_error("PHP::error removing folder : $message", EQ_REPORT_DEBUG);
                    // throw new Exception('fs_removing_file_failure', EQ_ERROR_UNKNOWN);
                }
            }

            // (re)create the (empty) folder
            if(!mkdir("public/$app")) {
                // trigger_error("PHP::error moving file : $message", EQ_REPORT_DEBUG);
                // throw new Exception('fs_moving_file_failure', EQ_ERROR_UNKNOWN);
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
        }
    }

    // 6) Inject composer dependencies if any
    if(isset($package_manifest['requires']) && is_array($package_manifest['requires'])) {
        $map_composer = [
            'require'     => [],
            'require-dev' => []
        ];

        if(file_exists(EQ_BASEDIR.'/composer.json')) {
            $json = file_get_contents(EQ_BASEDIR.'/composer.json');
            $data = json_decode($json, true);
            if($data) {
                $map_composer = $data;
            }
        }

        foreach($package_manifest['requires'] as $dependency => $version) {
            $map_composer['require'][$dependency] = $version;
        }

        // remove unused/empty
        if(empty($map_composer['require'])) {
            unset($map_composer['require']);
        }

        if(empty($map_composer['require-dev'])) {
            unset($map_composer['require-dev']);
        }

        file_put_contents(EQ_BASEDIR.'/composer.json', json_encode($map_composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // 7) Inject config values, if present
    if(isset($package_manifest['config']) && is_array($package_manifest['config'])) {
        $config = [];

        if(file_exists(EQ_BASEDIR.'/config/config.json')) {
            $json = file_get_contents(EQ_BASEDIR.'/config/config.json');
            $data = json_decode($json, true);
            if($data) {
                $config = $data;
            }
        }

        foreach($package_manifest['config'] as $constant => $value) {
            $config[$constant] = $value;
        }

        file_put_contents(EQ_BASEDIR.'/config/config.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }


    // mark the package as initialized (installed)

    /**
     * The list of installed package is maintained in the file /log/packages.json.
     * It is stored as an associative array mapping package names with the latest moment they were initialized.
     */
    $packages = [];

    if(file_exists("log/packages.json")) {
        $json = file_get_contents("log/packages.json");
        $packages = json_decode($json, true);
    }

    $packages[$params['package']] = date('c');
    file_put_contents("log/packages.json", json_encode($packages, JSON_PRETTY_PRINT));

    // if script is running at top-level, it must run composer to install vendor dependencies
    if($params['root'] && $params['composer']) {

        if(file_exists(EQ_BASEDIR.'/composer.json')) {
            $json = file_get_contents(EQ_BASEDIR.'/composer.json');
            $map_composer_current = json_decode($json, true);
        }

        if($map_composer_current != $map_composer_original) {
            eQual::run('do', 'init_composer');
        }
    }
}

$context->httpResponse()
        ->status(201)
        ->send();
