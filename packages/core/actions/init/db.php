<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnector;


list($params, $providers) = eQual::announce([
    'description'   => 'Create a database according to the configuration',
    'params'        => [],
    'providers'     => ['context', 'orm'],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS', 'DB_CHARSET']
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

eQual::run('do', 'test_db-connectivity');

// create Master database
$db = DBConnector::getInstance()->connect(false);
$db->createDatabase(constant('DB_NAME'));

// create replica members, if any
if(defined('DB_REPLICATION') && constant('DB_REPLICATION') != 'NO') {
    $i = 1;
    while(defined('DB_'.$i.'_HOST') && defined('DB_'.$i.'_PORT') && defined('DB_'.$i.'_USER') && defined('DB_'.$i.'_PASSWORD') && defined('DB_'.$i.'_NAME')) {
        $db = DBConnector::getInstance(constant('DB_'.$i.'_HOST'), constant('DB_'.$i.'_PORT'), constant('DB_'.$i.'_NAME'), constant('DB_'.$i.'_USER'), constant('DB_'.$i.'_PASSWORD'), constant('DB_DBMS'))->connect(false);
        $db->createDatabase(constant('DB_'.$i.'_NAME'));
        ++$i;
    }
}