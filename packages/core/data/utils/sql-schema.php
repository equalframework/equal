<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnection;

// get listing of existing packages
$json = run('get', 'config_packages');
$packages = json_decode($json, true);


list($params, $providers) = announce([
    'description'	=>	"Returns the schema of the specified package in standard SQL ('CREATE' statements with 'IF NOT EXISTS' clauses).",
    'params' 		=>	[
        'package'	=> [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string', 
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],    
    'providers'     => ['context', 'orm']     
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$params['package'] = strtolower($params['package']);


$json = run('do', 'test_db-access');
if(strlen($json)) {
    // relay result
    print($json);
    // return an error code
    exit(1);
}
// retrieve connection object
$db = DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS)->connect();


$result = array();

// get classes listing
$json = run('get', 'config_classes', ['package' => $params['package']]);
$data = json_decode($json, true);
// relay error if any
if(isset($data['errors'])) {
    foreach($data['errors'] as $name => $message) throw new Exception($message, qn_error_code($name));
}
else {
    $classes = $data;
}


$m2m_tables = array();

// remember tables used by inherited classes (can only be overriden by children, not by ancestor)
$parent_tables = [];

foreach($classes as $class) {
	// get the full class name
	$class_name = $params['package'].'\\'.$class;
    $model = $orm->getModel($class_name);    
    if(!is_object($model)) throw new Exception("unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);    	
    
    // get the SQL table name
    $table_name = $orm->getObjectTableName($class_name);	
    $direct_name = strtolower(str_replace('\\', '_', $class_name));    
 
    // skip tables used by inherited classes
    if(in_array($table_name, $parent_tables)) {        
// todo : means we need to use ALTER rather than CREATE TABLE        
// so we should rather run a sql query to check existing tables at each loop
        continue;
    }
     
    // handle inherited classes
    if($table_name != $direct_name) {
        // table name is the table of an ancestor
        $parent_tables[] = $table_name;
    }
    
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

	// init result array

// #todo : deleting tables prevents keeping data across inherited classes
    // $result[] = "DROP TABLE IF EXISTS `{$table_name}`;";
    
    // fetch existing column 
    $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND TABLE_SCHEMA='".DB_NAME."';";
    $res = $db->sendQuery($query);

    $columns = [];
    while ($row = $db->fetchArray($res)) {
        $columns[] = $row['COLUMN_NAME'];
    }

    // if some columns already exist (we are enriching a table related to a class from which the current class inherits),
    // then we append only the columns that do not exit yet
    if(!empty($columns)) {
        $columns_diff = array_diff(array_keys($schema), $columns);
        foreach($columns_diff as $field) {
            $description = $schema[$field];
            if(in_array($description['type'], array_keys(ObjectManager::$types_associations))) {
                $type = ObjectManager::$types_associations[$description['type']];
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
                if($field == 'id') $result[] = "ALTER TABLE `{$table_name}` ADD `{$field}` {$type} NOT NULL AUTO_INCREMENT;";
                elseif(in_array($field, array('creator','modifier','published','deleted'))) $result[] = "ALTER TABLE `{$table_name}` ADD `{$field}` {$type} NOT NULL DEFAULT '0';";
                else $result[] = "ALTER TABLE `{$table_name}` ADD `{$field}` {$type};";
            }
            else if( $description['type'] == 'computed' && isset($description['store']) && $description['store'] ) {
                $type = ObjectManager::$types_associations[$description['result_type']];
                $result[] = "ALTER TABLE `{$table_name}` ADD `{$field}` {$type} DEFAULT NULL;";
            }
            else if($description['type'] == 'many2many') {
                if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }

        }
    }
    // if no columns were found, the table is either empty or do not exist yet
    // in this case, we create the table and add all the columns
    else {
        $result[] = "CREATE TABLE IF NOT EXISTS `{$table_name}` (";
	
        foreach($schema as $field => $description) {
            if(in_array($description['type'], array_keys(ObjectManager::$types_associations))) {
                $type = ObjectManager::$types_associations[$description['type']];
    
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
    
                if($field == 'id') $result[] = "`{$field}` {$type} NOT NULL AUTO_INCREMENT,";
                elseif(in_array($field, array('creator','modifier','published','deleted'))) $result[] = "`{$field}` {$type} NOT NULL DEFAULT '0',";
                else $result[] = "`{$field}` {$type},";
            }
            else if( $description['type'] == 'computed' && isset($description['store']) && $description['store'] ) {
                $type = ObjectManager::$types_associations[$description['result_type']];
                $result[] = "`{$field}` {$type} DEFAULT NULL,";
            }
            else if($description['type'] == 'many2many') {
                if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
        }
        $result[] = "PRIMARY KEY (`id`)";
    
        if(method_exists($model, 'getUnique')) {
            $list = $model->getUnique();
            foreach($list as $fields) {
                $result[] = ",UNIQUE KEY `".implode('_', $fields)."` (`".implode('`,`', $fields)."`)";
            }
        
        }
        
        $result[] = ") DEFAULT CHARSET=utf8;\n";    
    }

}

foreach($m2m_tables as $table => $columns) {
	$result[] = "CREATE TABLE IF NOT EXISTS `{$table}` (";
	$key = '';
	foreach($columns as $column) {
		$result[] = "`{$column}` int(11) NOT NULL,";
		$key .= "`$column`,";
	}
	$key = rtrim($key, ",");
	$result[] = "PRIMARY KEY ({$key})";
	$result[] = ");";
	// add an empty records (mandatory for JOIN conditions on empty tables)
	$result[] = "INSERT IGNORE INTO `{$table}` (".implode(',', array_map(function($col) {return "`{$col}`";}, $columns)).') VALUES ';
	$result[]= '('.implode(',', array_fill(0, count($columns), 0)).");";
}

// send json result
$context->httpResponse()
        ->body(['result' => implode("\n", $result)])
        ->send();