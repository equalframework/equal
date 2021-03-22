<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use qinoa\db\DBConnection;


list($params, $providers) = announce([
    'description'   => 'Create a database according to the configuration',
    'params'        => [
    
    ],
    'providers'     => ['context', 'orm'],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$json = run('do', 'test_db-connectivity');

if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}

// create Master database
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect(false);
$db->sendQuery("CREATE DATABASE IF NOT EXISTS ".DB_NAME.";");

// create replica members, if any
if(defined('DB_REPLICATION') && DB_REPLICATION != 'NO') {
    $i = 1;
    while(defined('DB_'.$i.'_HOST') && defined('DB_'.$i.'_PORT') && defined('DB_'.$i.'_USER') && defined('DB_'.$i.'_PASSWORD') && defined('DB_'.$i.'_NAME')) {
        $db = DBConnection::getInstance(constant('DB_'.$i.'_HOST'), constant('DB_'.$i.'_PORT'), constant('DB_'.$i.'_NAME'), constant('DB_'.$i.'_USER'), constant('DB_'.$i.'_PASSWORD'), DB_DBMS)->connect(false);
        $db->sendQuery("CREATE DATABASE IF NOT EXISTS ".constant('DB_'.$i.'_NAME').";");        
        ++$i;
    }
}