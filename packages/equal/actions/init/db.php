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

// retrieve connection object
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect(false);

$db->sendQuery("CREATE DATABASE IF NOT EXISTS ".DB_NAME.";");