<?php
use qinoa\db\DBConnection;

// get listing of existing packages
$json = eQUal::run('get', 'qinoa_config_packages');
$packages = json_decode($json, true);

$params = announce([
    'description'   => 'Initialise database for core package',
    'params'        => [
        'package'	=> [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string', 
            'in'            => array_values($packages),
            'default'       => 'core'
        ]
    
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);


$json = run('do', 'test_db-access');

if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}

// retrieve connection object
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect();

// 1) retrieve schema for core package
$json = run('get', 'qinoa_utils_sql-schema', ['package'=>$params['package']]);
// decode json into an array
$data = json_decode($json, true);
// join all lines
$schema = str_replace(["\r","\n"], "", $data['result']);
// push each line/query into an array
$queries = explode(';', $schema);

// 2) populate core_user table with demo data
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