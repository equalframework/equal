<?php
use qinoa\db\DBConnection;

$params = announce([
    'description'   => 'Tests access to the database',
    'params'        => [],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

// retrieve connection object
$db = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);

// 1) test connectivity to DBMS service
$json = run('do', 'test_db-connectivity');
if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}
// 2) try to connect to DBMS 
if(!$db->connected()) {
    if($db->connect(false) == false) {
        throw new Exception('Unable to establish connection to DBMS host (wrong credentials)', QN_ERROR_INVALID_CONFIG);
    }
}
// 3) try to select specified DB
if(!$db->select(DB_NAME)) {
    $db->disconnect();
    throw new Exception('Unable to access database (not found)', QN_ERROR_INVALID_CONFIG);
}
// 3) everything went well: disconnect
$db->disconnect();

// no error, exit code will be 0