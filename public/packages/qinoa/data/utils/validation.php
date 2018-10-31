<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;
use qinoa\db\DBConnection;

// get listing of existing packages
$json = QNLib::run('get', 'qinoa_config_packages');
$packages = json_decode($json, true);


// announce script and fetch parameters values
list($params, $providers) = QNLib::announce([

    'description'	=>	"This script tests the given package and returns a report about found errors (if any).",
    'params' 		=>	[
                        'package'	=>  [
                            'description'   => 'Package to validate.',
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




// result of the tests : array containing errors (if no errors are found, array is empty)
$result = array();

// get classes listing
$json = QNLib::run('get', 'qinoa_config_classes', ['package' => $params['package']]);
$classes = json_decode($json, true);


/**
* TESTING FILES
*
*/
$lang_dir = "packages/{$params['package']}/i18n";
$view_dir = "packages/{$params['package']}/views";

$lang_list = array();
if( is_dir($lang_dir) && ($list = scandir($lang_dir)) ) {
    foreach($list as $node) {
        if(is_dir($lang_dir.'/'.$node) && !in_array($node, array('.', '..'))) $lang_list[] = $node;
    }
}
    
foreach($classes as $class) {
// todo :
	// get filename containing class
	// check PHP syntax
	// check match between namespace and package
	// check match between classname and filename
	
	// get the full class name
	$class_name = $params['package'].'\\'.$class;
    
    $model = $orm->getModel($class_name);    
    if(!is_object($model)) throw new Exception("unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);    
        
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

	// 1) check fields descriptions consistency
    $valid_types = array_merge($orm::$virtual_types, $orm::$simple_types, $orm::$complex_types);

	foreach($schema as $field => $description) {
		if(!isset($description['type'])) {
			$result[] = "Class $class: Missing 'type' attribute for field $field";
			continue;
		}
        if(!in_array($description['type'], $valid_types)) {
			$result[] = "Class $class: Invalid type '{$description['type']}' for field $field";
			continue;
        }
		if(!$orm::checkFieldAttributes($orm::$mandatory_attributes, $schema, $field)) {
			$result[] = "Class $class: Missing at least one mandatory attribute for field '$field' ({$description['type']}) - mandatory attributes are : ".implode(', ', ObjectManager::$mandatory_attributes[$description['type']]);	
			continue;
		}
		foreach($description as $attribute => $value) {
			if(!in_array($attribute, $orm::$valid_attributes[$description['type']])) {
				$result[] = "Class $class: Unknown attribute '$attribute' for field '$field' ({$description['type']}) - Possible attributes are : ".implode(', ', ObjectManager::$valid_attributes[$description['type']]);
			}
			if(in_array($attribute, array('store', 'multilang', 'search')) && $value !== true && $value !== false) {
				$result[] = "Class $class: Incompatible value for attribute $attribute in field $field of type {$description['type']} (possible attributes are : true, false)";
			}
		}
	}

// todo : 2) check presence of class definition files to which some field may be related to


    // 3) check if default views are present (form.default.html and list.default.html)
    if(!is_file("$view_dir/$class.form.default.html"))
		$result[] = "Class $class: missing default form view (/views/$class.form.default.html)";
    if(!is_file("$view_dir/$class.list.default.html"))
		$result[] = "Class $class: missing default list view (/views/$class.list.default.html)";

    // 4) check if translation file are present (.json)
    foreach($lang_list as $lang) {
         if(!is_file("$lang_dir/$lang/$class.json")) {
             $result[] = "Class $class: missing translation file for language $lang";
         }
    }

// todo : 5) check view files consistency (.html)
// todo : 6) check translation file consistency (.json)
	
// todo : 7) check apps / data / actions  files consistency (.php) 
    // - presence of " defined('__QN_LIB') or die "
    // - presence of " = announce( "
	
}



/**
* TESTING DATABASE
*
*/

// 1) check that the DB table exists
// 2) check that the fields exists in DB
// 3) check types compatibility
// 4) check that the DB table exists

	// a) check that every declared simple field is present in the associated DB table
	// b) check that relational tables, if any, are present as well


if(is_file('packages/'.$params['package'].'/config.inc.php')) include('packages/'.$params['package'].'/config.inc.php');
// reminder: constants already exported : cannot redefine constants using config\export_config()

$db = &DBConnection::getInstance(config('DB_HOST', DB_HOST), config('DB_PORT', DB_PORT), config('DB_NAME', DB_NAME), config('DB_USER', DB_USER), config('DB_PASSWORD',DB_PASSWORD), config('DB_DBMS',DB_DBMS));

if(!$db->is_connected()) {
    if($db->connect() == false) {
        die('Error: Unable to connect to database');
    }
}
                
$allowed_associations = array(
	'boolean' 		=> array('bool', 'tinyint', 'smallint', 'mediumint', 'int', 'bigint'),
	'integer' 		=> array('tinyint', 'smallint', 'mediumint', 'int', 'bigint'),
	'float' 		=> array('float', 'decimal'),	
	'string' 		=> array('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'blob', 'mediumblob'),
	'text' 			=> array('tinytext', 'text', 'mediumtext', 'longtext', 'blob'),
	'html' 			=> array('tinytext', 'text', 'mediumtext', 'longtext', 'blob'),    
	'date' 			=> array('date', 'datetime'),
	'time' 			=> array('time'),
	'datetime' 		=> array('datetime'),
	'timestamp' 	=> array('timestamp'),
	'selection' 	=> array('char', 'varchar'),
	'file'  		=> array('blob', 'mediumblob', 'longblob'),    
	'binary' 		=> array('blob', 'mediumblob', 'longblob'),
	'many2one' 		=> array('int')
);

// load database tables
$tables = array();
$res = $db->sendQuery("show tables;");
while($row = $db->fetchRow($res)) {
	$tables[$row[0]] = true;
}


foreach($classes as $class) {
	// get the full class name
	$class_name = $params['package'].'\\'.$class;

        $model = $orm->getModel($class_name);    
    if(!is_object($model)) throw new Exception("unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);    
        
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

	// get the SQL table name
	$table = $orm->getObjectTableName($class_name);	
    
	// 1) verify that the DB table exists
	if(!isset($tables[$table])) {
		$result[] = "Class $class: Associated table ({$table}) does not exist in database";
		continue;
	}

	// load DB schema
	$db_schema = array();
	$res = $db->sendQuery("show full columns from `{$table}`;");
	while($row = $db->fetchArray($res)) {
		// we dont need the length, if present
		$db_type = explode('(', $row['Type']);
		$db_schema[$row['Field']] = array('type'=>$db_type[0]);
	}

	$simple_fields = array();
	$m2m_fields = array();
	foreach($schema as $field => $description) {
		if(in_array($description['type'], $orm::$simple_types)) $simple_fields[] = $field;
		// handle the 'store' attrbute
		else if(in_array($description['type'], array('function', 'related'))) {
			if(isset($description['store']) && $description['store']) $simple_fields[] = $field;
		}
		else if($description['type'] == 'many2many') $m2m_fields[] = $field;
	}
	// a) check that every declared simple field is present in the associated DB table
	foreach($simple_fields as $field) {
		// 2) verify that the fields exists in DB
		if(!isset($db_schema[$field])) $result[] = "Class $class: Field $field ({$schema[$field]['type']}) does not exist in table {$table}";
		else {
		// 3) verify types compatibility
			$type = $schema[$field]['type'];
			if(in_array($type, array('function', 'related'))) $type = $schema[$field]['result_type'];
			if(!in_array($db_schema[$field]['type'], $allowed_associations[$type])) {
				$result[] = "Class $class: Non compatible type in database ({$db_schema[$field]['type']}) for field $field ({$schema[$field]['type']})";
			}
		}
	}
	// b) check that relational tables, if any, are present as well
	foreach($m2m_fields as $field) {
		// 4) verify that the DB table exists
		$table_name = $schema[$field]['rel_table'];

		if(!isset($tables[$table_name])) {
			$result[] = "Class $class: Relational table ($table_name) specified by field {$field} does not exist in database";
		}
	}
}


if(!count($result)) $result[] = "No errors found.";

// send json result
$context->httpResponse()
        ->body(['result' => $result])
        ->send();