<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnection;

// get listing of existing packages
$json = run('get', 'config_packages');
$data = json_decode($json, true);
if($data && isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) throw new Exception($message, qn_error_code($name));
}
$packages = $data;

list($params, $providers) = announce([
    'description'   => 'Initialise database for given package. If no package is given, initialize core package.',
    'params'        => [
        'package' => [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string',
            'in'            => array_values($packages),
            'default'       => 'core'
        ],
        'import' => [
            'description'   => 'Flag to import the initial data.',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'providers'     => ['context', 'orm', 'adapt'],
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

$json = run('do', 'test_db-access');
if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}
// retrieve connection object
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect();


// PASS-ONE : init DB with SQL schema


// 1) retrieve schema for given package
$json = run('get', 'utils_sql-schema', ['package'=>$params['package']]);
// decode json into an array
$data = json_decode($json, true);
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) throw new Exception($message, qn_error_code($name));
}
// join all lines
$schema = str_replace(["\r","\n"], "", $data['result']);
// push each line/query into an array
$queries = explode(';', $schema);

// send each query to the DBMS
foreach($queries as $query) {
    if(strlen($query)) {
        $db->sendQuery($query.';');
    }
}

// PASS-TWO: check for missing columns
// for classes with inheritance, we must check against non yet created fields
$queries = [];


// 2) retrieve classes listing
$json = run('get', 'config_classes', ['package' => $params['package']]);
$data = json_decode($json, true);
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) throw new Exception($message, qn_error_code($name));
}
else {
    $classes = $data;
}

$types_associations = array(
    'boolean' 		=> 'tinyint(4)',
    'integer' 		=> 'int(11)',
    'float' 		=> 'decimal(10,2)',
    'string' 		=> 'varchar(255)',
    'text' 			=> 'mediumtext',
    'html' 			=> 'mediumtext',
    'date' 			=> 'date',
    'time' 			=> 'time',
    'datetime' 		=> 'datetime',
    'timestamp' 	=> 'timestamp',
    'file' 		    => 'mediumblob',
    'binary' 		=> 'mediumblob',
    'many2one' 		=> 'int(11)'
);



$m2m_tables = array();

// tables have been created, but fields in inherited classes might still be missing
foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;
    $model = $orm->getModel($entity);

//    if(basename(get_parent_class($model)) == 'Model') {
        $table_name = $orm->getObjectTableName($entity);
        $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND TABLE_SCHEMA='".DB_NAME."';";
        $res = $db->sendQuery($query);
        $columns = [];
        while ($row = $db->fetchArray($res)) {
            // maintain ids order provided by the SQL sort
            $columns[] = $row['COLUMN_NAME'];
        }
        // retrieve fields that need to be added
        $schema = $model->getSchema();
        // retrieve list of fields that must be added to the schema
        $diff = array_diff(array_keys(array_filter($schema, function($a) use($orm) {return in_array($a['type'], $orm::$simple_types); })), $columns);

        // init result array
        $result = [];
        foreach($diff as $field) {
            $description = $schema[$field];
            if(in_array($description['type'], array_keys($types_associations))) {
                $type = $types_associations[$description['type']];
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
                $result[] = "ALTER TABLE `{$table_name}` ADD COLUMN `{$field}` {$type}";
            }
            else if($description['type'] == 'computed' && isset($description['store']) && $description['store']) {
                $type = $types_associations[$description['result_type']];
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
                $result[] = "ALTER TABLE `{$table_name}` ADD COLUMN  `{$field}` {$type} DEFAULT NULL";
            }
            else if($description['type'] == 'many2many') {
                if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
        }
        if(count($result)) {
            $queries = array_merge($queries, $result);
        }
//    }
}


// 3) add missing relation tables, if any
foreach($m2m_tables as $table => $columns) {
    $query = "CREATE TABLE IF NOT EXISTS `{$table}` (";
    $key = '';
    foreach($columns as $column) {
        $query .= "`{$column}` int(11) NOT NULL,";
        $key .= "`$column`,";
    }
    $key = rtrim($key, ",");
    $query .= "PRIMARY KEY ({$key})";
    $query .= ")";
    $queries[] = $query;
    // add an empty records (mandatory for JOIN conditions on empty tables)
    $query = "INSERT IGNORE INTO `{$table}` (".implode(',', array_map(function($col) {return "`{$col}`";}, $columns)).') VALUES ';
    $query .= '('.implode(',', array_fill(0, count($columns), 0)).");\n";
    $queries[] = $query;
}


// #todo : make distinction between mandatory initial data and demo data


// 4) populate tables with predefined data
$data_folder = "packages/{$params['package']}/init/data";
if($params['import'] && file_exists($data_folder) && is_dir($data_folder)) {
    // handle SQL files
    foreach (glob($data_folder."/*.sql") as $data_sql) {
        $queries = array_merge($queries, file($data_sql, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }
    // send each query to the DBMS
    foreach($queries as $query) {
        if(strlen($query)) {
            $db->sendQuery($query.';');
        }
    }

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
                $res = $orm->create($entity, $odata, $lang);
                if($res == QN_ERROR_CONFLICT_OBJECT) {
                    $id = $odata['id'];
                    // object already exist, but either values or language differs
                    $res = $orm->write($entity, $id, $odata, $lang);
                    $objects_ids[] = $id;
                }
                else {
                    $objects_ids[] = $res;
                }
            }

            // force a first computation of computed fields, if any
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


// 5) if a `bin` folder exists, copy its content to /bin/<package>/
$bin_folder = "packages/{$params['package']}/init/bin";
if(file_exists($bin_folder) && is_dir($bin_folder)) {
    exec("cp -r $bin_folder bin/{$params['package']}");
    exec("chown www-data:www-data -R bin/{$params['package']}");
}

// 6) if a `routes` folder exists, copy its content to /config/routing/
$route_folder = "packages/{$params['package']}/init/routes";
if(file_exists($route_folder) && is_dir($route_folder)) {
    exec("cp -r $route_folder/* config/routing");
}

$context->httpResponse()
        ->status(201)
        ->send();