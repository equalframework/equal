<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

// get listing of existing packages
$json = run('get', 'qinoa_config_packages');
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
    'providers'     => ['context', 'orm']     
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$params['package'] = strtolower($params['package']);

$result = array();

// get classes listing
$json = run('get', 'qinoa_config_classes', ['package' => $params['package']]);
$classes = json_decode($json, true);
    

$types_associations = [
	'boolean' 		=> 'tinyint(4)',
	'integer' 		=> 'int(11)',
	'float' 		=> 'decimal(10,2)',	
	'string' 		=> 'varchar(255)',
	'text' 			=> 'mediumtext',
	'html' 			=> 'mediumtext',    
	'date' 			=> 'date',
	'time' 			=> 'time',
	'datetime' 		=> 'datetime',
	'timestamp' 	=> 'timestamp',
	'file' 		    => 'mediumblob',    
	'binary' 		=> 'mediumblob',
	'many2one' 		=> 'int(11)'
];

$usages_associations = [
	'coordinate/decimal'	=> 'decimal(9,6)',
	'language/iso-639:2'	=> 'char(2)',
	'amount/money'			=> 'decimal(15,2)'
];

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
    $result[] = "DROP TABLE IF EXISTS `{$table_name}`;";
	$result[] = "CREATE TABLE IF NOT EXISTS `{$table_name}` (";
	
	foreach($schema as $field => $description) {
		if(in_array($description['type'], array_keys($types_associations))) {
			$type = $types_associations[$description['type']];

			if( isset($description['usage']) && isset($usages_associations[$description['usage']]) ) {
				$type = $usages_associations[$description['usage']];
			}

			if($field == 'id') $result[] = "`{$field}` {$type} NOT NULL AUTO_INCREMENT,";
			elseif(in_array($field, array('creator','modifier','published','deleted'))) $result[] = "`{$field}` {$type} NOT NULL DEFAULT '0',";
			else $result[] = "`{$field}` {$type},";
		}
		else if($description['type'] == 'function' && isset($description['store']) && $description['store']) {
			$type = $types_associations[$description['result_type']];
			$result[] = "`{$field}` {$type} DEFAULT NULL,";
		}
		else if($description['type'] == 'many2many') {
			if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
		}
	}
	$result[] = "PRIMARY KEY (`id`)";

    if(method_exists($model, 'getUnique')) {
        $list = $model::getUnique();
        foreach($list as $fields) {
            $result[] = ",\nUNIQUE KEY `{$fields[0]}` (`".implode('`,`', $fields)."`)";
        }
    
    }
    
	$result[] = ") DEFAULT CHARSET=utf8;\n";
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
	$result[] = ");\n";
	// add an empty records (mandatory for JOIN conditions on empty tables)
	$result[] = "INSERT IGNORE INTO `{$table}` (".implode(',', array_map(function($col) {return "`{$col}`";}, $columns)).') VALUES ';
	$result[]= '('.implode(',', array_fill(0, count($columns), 0)).");\n";
}

// send json result
$context->httpResponse()
        ->body(['result' => implode("\n", $result)])
        ->send();