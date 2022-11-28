<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnection;
use equal\fs\FSManipulator as FS;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

list($params, $providers) = announce([
    'description'   => 'Initialise database for given package. If no package is given, initialize core package.',
    'params'        => [
        'package' => [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string',
            'in'            => array_values($packages),
            'default'       => 'core'
        ],
        'cascade' => [
            'description'   => 'Cascade initialisation of the packages marked as dependencies.',
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
 * @var equal\php\Context                   $context
 * @var equal\orm\ObjectManager             $orm
 * @var equal\data\DataAdapter              $adapter
 */
list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

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

// 1) Check in manifest.json for prerequisite initialisation

if(!isset($GLOBALS['QN_INIT_DEPENDENCIES'])) {
    $GLOBALS['QN_INIT_DEPENDENCIES'] = [];
}

// mark current package as being initialised (to prevent recursion)
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
            // init package of sub packages (perform as root script)
            eQual::run('do', 'init_package', [
                    'package'           => $dependency,
                    'cascade'           => $params['cascade'],
                    'import'            => $params['import'] && $params['import_cascade']
                ],
                true);
        }
    }
}

// 1) Init DB with SQL schema

/*  start-tables_init */

// retrieve schema for given package
$data = eQual::run('get', 'utils_sql-schema', ['package' => $params['package']]);

// push each line/query into an array
$queries = explode(";", $data['result']);

// send first batch of queries to the DBMS
foreach($queries as $query) {
    $db->sendQuery($query);
}

// check for missing columns (for classes with inheritance, we must check against non-yet created fields)
$queries = [];

// retrieve classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

$m2m_tables = [];

// tables have been created, but fields in inherited classes might still be missing
foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;
    $model = $orm->getModel($entity);

    $table_name = $orm->getObjectTableName($entity);

    // fetch existing column
    $existing_columns = $db->getTableColumns($table_name);

    // retrieve fields that need to be added
    $schema = $model->getSchema();
    // retrieve list of fields that must be added to the schema
    $diff = array_diff(array_keys(array_filter($schema, function($a) use($orm) {return in_array($a['type'], $orm::$simple_types); })), $existing_columns);

    foreach($diff as $field) {
        if(in_array($field, $existing_columns)) {
            continue;
        }
        $description = $schema[$field];
        if(in_array($description['type'], array_keys($db_class::$types_associations))) {
            $type = $db->getSqlType($description['type']);

            // if a SQL type is associated to field 'usage', it prevails over the type association
            if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                // $type = ObjectManager::$usages_associations[$description['usage']];
            }
            $queries[] = $db->getQueryAddColumn($table_name, $field, [
                'type'      => $type,
                'null'      => true,
                'default'   => null
            ]);
        }
        elseif($description['type'] == 'computed' && isset($description['store']) && $description['store']) {
            $type = $db->getSqlType($description['result_type']);
            $queries[] = $db->getQueryAddColumn($table_name, $field, [
                'type'      => $type,
                'null'      => true,
                'default'   => null
            ]);
        }
        elseif($description['type'] == 'many2many') {
            if(!isset($m2m_tables[$description['rel_table']])) {
                $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
        }
    }
}

// add missing relation tables, if any
foreach($m2m_tables as $table => $columns) {
    $constraint_name = implode('_', $columns);
    $existing_constraints = $db->getTableConstraints($table);
    if(in_array($constraint_name, $existing_constraints)) {
        continue;
    }
    // fetch existing columns
    $existing_columns = $db->getTableColumns($table);
    // create table if not exist
    $queries[] = $db->getQueryCreateTable($table);
    foreach($columns as $column) {
        if(in_array($column, $existing_columns)) {
            continue;
        }
        $type = $db->getSqlType('integer');
        $queries[] = $db->getQueryAddColumn($table, $column, [
            'type'      => $type,
            'null'      => false
        ]);
    }
    $queries[] = $db->getQueryAddConstraint($table, $columns);
    // add an empty record (required for JOIN conditions on empty tables)
    $queries[] = $db->getQueryAddRecords($table, $columns, [array_fill(0, count($columns), 0)]);
}

// send each query to the DBMS
foreach($queries as $query) {
    $db->sendQuery($query);
}


/*  end-tables_init */

// #todo : make distinction between mandatory initial data and demo data

// 2) Populate tables with predefined data
$data_folder = "packages/{$params['package']}/init/data";
if($params['import'] && file_exists($data_folder) && is_dir($data_folder)) {
    // handle SQL files
    /*
    foreach (glob($data_folder."/*.sql") as $data_sql) {
        $queries = array_merge($queries, file($data_sql, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }
    // send each query to the DBMS
    foreach($queries as $query) {
        if(strlen($query)) {
            $db->sendQuery($query.';');
        }
    }
    */

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
                    $odata[$field] = $adapter->adapt($value, $schema[$field]['type']);
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
                // trigger_error("QN_DEBUG_PHP::error removing folder : $message", QN_REPORT_DEBUG);
                // throw new Exception('fs_removing_file_failure', QN_ERROR_UNKNOWN);
            }
        }

        // (re)create the (empty) folder
        if(!mkdir("public/$app")) {
            // trigger_error("QN_DEBUG_PHP::error moving file : $message", QN_REPORT_DEBUG);
            // throw new Exception('fs_moving_file_failure', QN_ERROR_UNKNOWN);
        }

        // verify the checksum
        if(isset($app_manifest['checksum'])) {
            $md5 = md5_file("$app_path/web.app");
            if($md5 != $app_manifest['checksum']) {
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
    }
}

$context->httpResponse()
        ->status(201)
        ->send();