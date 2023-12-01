<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnection;

$params = announce([
    'description'   => "Tests connectivity to the DBMS server.\nIn case of success, the script simply terminates with a status code of 0 (no output)",
    'params'        => [],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

// retrieve connection object
$db = DBConnection::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'));

// 1) test access to DBMS service
if(!$db->canConnect()) {
    throw new Exception('Unable to establish connection to DBMS host (wrong hostname or port).', QN_ERROR_INVALID_CONFIG);
}
// 2) try to connect to DBMS
if(!$db->connected()) {
    if($db->connect(false) == false) {
        throw new Exception('Unable to establish connection to DBMS host (wrong credentials).', QN_ERROR_INVALID_CONFIG);
    }
}
// 3) everything went well: disconnect
$db->disconnect();

// no error, exit code will be 0