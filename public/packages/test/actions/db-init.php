<?php
use qinoa\db\DBConnection;

// get listing of existing packages
$json = eQUal::run('get', 'qinoa_config_packages');
$packages = json_decode($json, true);

$params = eQual::announce([
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

// retrieve connection object
$db = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);

$json = eQual::run('get', 'test_db-access');

if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}
// retrieve schema for core package
$json = eQual::run('get', 'qinoa_utils_sql-schema', ['package'=>$params['package']]);
// decode json into an array
$data = json_decode($json, true);
// join all lines
$schema = str_replace(["\r","\n"], "", $data['result']);
// push each line/query into an array
$queries = explode(';', $schema);


// populate core_user table with demo data
if($params['package'] == 'core') {
    $queries[] = "INSERT INTO `core_user` (`id`, `created`, `modified`, `creator`, `modifier`, `state`, `deleted`, `login`, `password`, `firstname`, `lastname`, `start`, `language`, `validated`) VALUES (1, '2012-08-18 15:06:43', '2012-08-18 15:06:43', 1, 1, 'instance', 0, 'root@example.com', 'd9c04b93c8dd7ce461b851952d7d06f3', 'root', '@system', '', 'en', 1)";
    $queries[] = "INSERT INTO `core_user` (`id`, `created`, `modified`, `creator`, `modifier`, `state`, `deleted`, `login`, `password`, `firstname`, `lastname`, `start`, `language`, `validated`) VALUES (2, '2012-08-18 15:06:43', '2014-09-04 12:21:34', 1, 1, 'instance', 0, 'cedricfrancoys@gmail.com', '02e5408967241673cd03126fe55dcd1a', 'Cédric', 'FRANÇOYS', 'core_manage', 'fr', 1)";
    $queries[] = "INSERT INTO `core_group` (`id`, `created`, `modified`, `creator`, `modifier`, `state`, `deleted`, `name`) VALUES (1, '2012-05-30 20:45:20', '2012-05-30 20:45:20', 2, 2, '', 0, 'default')";
    $queries[] = "INSERT INTO `core_rel_group_user` (`group_id`, `user_id`) VALUES (1, 1)";
    $queries[] = "INSERT INTO `core_rel_group_user` (`group_id`, `user_id`) VALUES (1, 2)";
}


// send each query to the DBMS
foreach($queries as $query) {
    if(strlen($query)) {
        $db->sendQuery($query.';');
    }
}