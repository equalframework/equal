<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnector;

$params = announce([
    'description'   => "Tests access to the database.\nIn case of success, the script simply terminates with a status code of 0 (no output).",
    'params'        => [],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

// retrieve connection object
$db = DBConnector::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'));

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
if(!$db->select(constant('DB_NAME'))) {
    $db->disconnect();
    throw new Exception('Unable to access specified database (DB \''.constant('DB_NAME').'\' not found)', QN_ERROR_INVALID_CONFIG);
}
// 3) everything went well: disconnect
$db->disconnect();

// no error, exit code will be 0