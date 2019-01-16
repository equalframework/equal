<?php
use qinoa\db\DBConnection;

$params = eQual::announce([
    'description'   => 'Tests access to the database',
    'params'        => [],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

// retrieve connection object
$db = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);

$json = eQual::run('get', 'test_db-connectivity');

if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}

// 3) try to select specified DB
if(!$db->select(DB_NAME)) {
    $db->disconnect();
    throw new Exception('Unable to establish connection to DBMS host (database not found)', QN_ERROR_INVALID_CONFIG);
}

// no error, exit code will be 0