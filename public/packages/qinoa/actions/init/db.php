<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\db\DBConnection;

// get listing of existing packages
$json = run('get', 'qinoa_config_packages');
$packages = json_decode($json, true);

list($params, $providers) = announce([
    'description'   => 'Initialise database for given package. If no package is given, initialize core package.',
    'params'        => [
        'package'	=> [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string', 
            'in'            => array_values($packages),
            'default'       => 'core'
        ]
    
    ],
    'providers'     => ['context', 'orm'],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$json = run('do', 'test_db-access');

if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}


// retrieve connection object
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect();


// 0) check if package has already been initialized
/*
$query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME like '{$params['package']}_%';";
$res = $db->sendQuery($query);

if ($row = $db->fetchArray($res)) {
    // package already initialized
    // todo: this does not cover all cases : e.g. a package having exclusively classes inherited from another package
}
else {
*/
    // PASS-ONE : execute schema SQL
    
    // 1) retrieve schema for given package
    $json = run('get', 'qinoa_utils_sql-schema', ['package'=>$params['package']]);
    // decode json into an array
    $data = json_decode($json, true);
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
    $json = run('get', 'qinoa_config_classes', ['package' => $params['package']]);
    $classes = json_decode($json, true);


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
    
    foreach($classes as $class) {
        // get the full class name
        $class_name = $params['package'].'\\'.$class;
        $model = $orm->getModel($class_name);
        if(basename(get_parent_class($model)) != 'Model') {
            $table_name = $orm->getObjectTableName($class_name);	
            $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name';";
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
                    $result[] = "ALTER TABLE `{$table_name}` ADD COLUMN `{$field}` {$type}";
                }
                else if($description['type'] == 'function' && isset($description['store']) && $description['store']) {
                    $type = $types_associations[$description['result_type']];
                    $result[] = "ALTER TABLE `{$table_name}` ADD COLUMN  `{$field}` {$type} DEFAULT NULL";
                }
                else if($description['type'] == 'many2many') {
                    if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
                }
            }
            if(count($result)) {
                $queries = array_merge($queries, $result);
            }
        }
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
            


    // 4) populate tables with demo data
    $data_sql = "packages/{$params['package']}/init/data.sql";
    if(file_exists($data_sql)) {
        $queries = array_merge($queries, explode(PHP_EOL, file_get_contents($data_sql)));
    }

    // send each query to the DBMS
    foreach($queries as $query) {
        if(strlen($query)) {
            $db->sendQuery($query.';');
        }
    }
/*	
}
*/