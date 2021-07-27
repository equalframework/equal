<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnection;

// get listing of existing packages
$json = run('get', 'config_packages');
$packages = json_decode($json, true);


// announce script and fetch parameters values
list($params, $providers) = announce([

    'description'	=>	"This script tests the given package and returns a report about found errors (if any).",
    'params' 		=>	[
        'package'	=>  [
            'description'   => 'Package to validate.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'level'	=>  [
            'description'   => 'Level of notification to limit the output to.',
            'type'          => 'string',
            'selection'     => ['*', 'warn', 'error'],
            'default'       => '*'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],    
    'providers'     => ['context', 'orm'],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS']    
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$params['package'] = strtolower($params['package']);


/*

TODO : check config and json files syntax.

translation files constraints: 
    * model helpers : max 45 chars
    * error messages length : max 45 chars
*/



// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];
$is_error = false;

// get classes listing
$json = run('get', 'config_classes', ['package' => $params['package']]);
$classes = json_decode($json, true);

// relay error if any
if(isset($classes['errors'])) {
    foreach($classes['errors'] as $name => $message) {
        throw new Exception($message, qn_error_code($name));
    }
}


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
// #todo - check match between namespace and package
// #todo - check match between classname and filename
    
    // get the full class name
    $class_name = $params['package'].'\\'.$class;
    // get the related filename
    $class_filename = str_replace('\\', '/', "packages/{$params['package']}/classes/{$class}".'.class.php');
    // check file existence
    if(!file_exists($class_filename)) {
        throw new Exception("FATAL - class defintion file missing for '{$class_name}' ", QN_ERROR_UNKNOWN_OBJECT);
    }

    // check PHP syntax
    $output = shell_exec(sprintf('php -l %s 2>&1', escapeshellarg($class_filename)));
    if(!preg_match('!No syntax errors detected!', $output)) {
        throw new Exception("FATAL - syntax error found in '{$class_name}' class definition file: {$output} ", QN_ERROR_UNKNOWN);
    }
// #todo - an Exception may still arise while loading a class dependency (of the same package)
    $model = $orm->getModel($class_name);
    
    if(!$model || !is_object($model)) {
        throw new Exception("FATAL - unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);
    }
        
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // 1) check fields descriptions consistency
    $valid_types = array_merge($orm::$virtual_types, $orm::$simple_types, $orm::$complex_types);

    foreach($schema as $field => $description) {
        if(!isset($description['type'])) {
            $result[] = "ERROR - ORM - Class $class: Missing 'type' attribute for field $field";
            $is_error = true;
            continue;
        }
        if(!in_array($description['type'], $valid_types)) {
            $result[] = "ERROR - ORM - Class $class: Invalid type '{$description['type']}' for field $field";
            $is_error = true;
            continue;
        }
        if(!$orm::checkFieldAttributes($orm::$mandatory_attributes, $schema, $field)) {
            $result[] = "ERROR - ORM - Class $class: Missing at least one mandatory attribute for field '$field' ({$description['type']}) - mandatory attributes are : ".implode(', ', $orm::$mandatory_attributes[$description['type']]);	
            $is_error = true;
            continue;
        }
        foreach($description as $attribute => $value) {
            if(!in_array($attribute, $orm::$valid_attributes[$description['type']])) {
                $result[] = "ERROR - ORM - Class $class: Unknown attribute '$attribute' for field '$field' ({$description['type']}) - Possible attributes are : ".implode(', ', $orm::$valid_attributes[$description['type']]);
                $is_error = true;
            }
            if(in_array($attribute, array('store', 'multilang', 'search')) && $value !== true && $value !== false) {
                $result[] = "ERROR - ORM - Class $class: Incompatible value for attribute $attribute in field $field of type {$description['type']} (possible attributes are : true, false)";
                $is_error = true;
            }
            if($attribute == 'foreign_object' && !class_exists($value))  {
                $result[] = "ERROR - ORM - Class $class: Non-existing entity '{$value}' given for attribute $attribute in field $field of type {$description['type']}";
                $is_error = true;
            }
        }
    }

// #todo - 2) check presence of class definition files to which some field may be related to + domain validity (ref to other object), when present


    // 3) check if default views are present (form.default.json and list.default.json)
    if(!is_file("$view_dir/$class.form.default.json")) {
        $result[] = "ERROR - UI - Class $class: missing default form view (/views/$class.form.default.json)";
    }
    if(!is_file("$view_dir/$class.list.default.json")) {
        $result[] = "ERROR - UI - Class $class: missing default list view (/views/$class.list.default.json)";
    }

    // 4) check if translation file are present (.json)
    foreach($lang_list as $lang) {
         if(!is_file("$lang_dir/$lang/$class.json")) {
             $result[] = "WARN - I18N - Class $class: missing translation file for language $lang";
         }
    }

    // #todo - 5) check view files consistency (.json)
    $view_files = glob("$view_dir/$class.*.json");
    
    foreach($view_files as $view_file) {
        $json = file_get_contents($view_file);
        $data = @json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $result[] = "ERROR - UI - Syntax error in file: $view_file";
            $is_error = true;
            continue;
        }
        if(strpos($view_file, 'form.') > 0) {
            $structure = [
                'name', 
                'description', 
                'layout' => [
                    'groups' => [
                        'sections' => [
                            'rows' => [
                                'columns' => [
                                    'width', 
                                    'items' => [
                                        'type', 'value', 'width'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];    
        }
        else {
            $structure = [
                'name', 
                'description', 
                'layout' => [
                    'items' => [
                        'type', 'value', 'width'
                    ]
                ]
            ];
        }
        // check that mandatory properties are present in the view
        $res = view_test($data, $structure);
        if($res) {
            $result[] = "ERROR - UI - ".$res." in file: $view_file";
            $is_error = true;
        }
        // check that fields targeted in views are valid (defined in schema)
        $items = view_get_items($data, $structure);
        foreach($items as $item) {
            if(isset($item['type']) && $item['type'] == 'field' && isset($item['value'])) {                
                $field = $item['value'];
                if(!isset($schema[$field])) {
                    $result[] = "ERROR - UI - Unknown field '$field' referenced in file $view_file";
                    $is_error = true;
                }
            }
        }
    }

    // 6) check translation file consistency (.json)	
    foreach($lang_list as $lang) {
        $i18n_file = "$lang_dir/$lang/$class.json";
        if(is_file($i18n_file)) {
            $json = file_get_contents($i18n_file);
            $data = @json_decode($json, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $result[] = "ERROR - I18N - Syntax error in file: $i18n_file";
                $is_error = true;
                continue;
            }
            $mandatory_properties = ['name', 'plural', 'description', 'model', 'view'];
            foreach($mandatory_properties as $property) {
                if(!isset($data[$property])) {
                    $result[] = "ERROR - I18N - Missing mandatory property '$property' in file: $i18n_file";
                    $is_error = true;
                }
            }            
            if(isset($data['model'])) {
                $fields = array_keys($data['model']);
                // check that the referenced fields are valid (defined in the schema)
                foreach($fields as $field) {
                    if(!isset($schema[$field])) {
                        $result[] = "ERROR - I18N - Unknown field '$field' referenced in file $i18n_file";
                        $is_error = true;
                    }
                }
                // check that the transaltion description is complete for each field
                foreach($fields as $field) {
                    $mandatory_properties = ['label', 'help'];
                    foreach($mandatory_properties as $property) {
                        if(!isset($data['model'][$field][$property])) {
                            $result[] = "WARN - I18N - Missing property '$property' for field '$field' referenced in file $i18n_file";
                        }
                        else {
                            if(!ctype_upper(substr($data['model'][$field][$property], 0, 1))) {
                                $result[] = "WARN - I18N - Value for property '$property' should start with uppercase for field '$field' referenced in file $i18n_file";
                            }

                            if($property == 'label') {
                                if(substr($data['model'][$field][$property], -1) == '.') {
                                    $result[] = "WARN - I18N - Value for property '$property' should not end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                            else if($property == 'help') {
                                if(substr($data['model'][$field][$property], -1) != '.') {
                                    $result[] = "WARN - I18N - Value for property '$property' should end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                        }
                    }
                }
            }
        }
   }
    
// todo : 7) check apps / data / actions  files consistency (.php) 
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

$db = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);

if(!$db->connected()) {
    if($db->connect() == false) {
        die('FATAL - Unable to connect to database');
    }
}

// load database tables
$tables = array();
$res = $db->sendQuery("show tables;");
while($row = $db->fetchRow($res)) {
    $tables[$row[0]] = true;
}

$allowed_types_associations = [
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
];

foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;

    $model = $orm->getModel($entity);    
    if(!is_object($model)) throw new Exception("unknown class '{$entity}'", QN_ERROR_UNKNOWN_OBJECT);
        
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // get the SQL table name
    $table = $orm->getObjectTableName($entity);	
    
    // 1) verify that the DB table exists
    if(!isset($tables[$table])) {
        $result[] = "ERROR - DB - Class $class: Associated table ({$table}) does not exist in database";
        $is_error = true;
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
        else if(in_array($description['type'], array('computed', 'related'))) {
            if(isset($description['store']) && $description['store']) $simple_fields[] = $field;
        }
        else if($description['type'] == 'many2many') $m2m_fields[] = $field;
    }
    // a) check that every declared simple field is present in the associated DB table
    foreach($simple_fields as $field) {
        // 2) verify that the fields exists in DB
        if(!isset($db_schema[$field])) {
            $result[] = "ERROR - DB - Class $class: Field $field ({$schema[$field]['type']}) does not exist in table {$table}";
            $is_error = true;
        }
        else {
        // 3) verify types compatibility
            $type = $schema[$field]['type'];
            if(in_array($type, array('computed', 'related'))) $type = $schema[$field]['result_type'];
            if(!in_array($db_schema[$field]['type'], $allowed_types_associations[$type])) {
                $result[] = "ERROR - DB - Class $class: Non compatible type in database ({$db_schema[$field]['type']}) for field $field ({$schema[$field]['type']})";
                $is_error = true;
            }
        }
    }
    // b) check that relational tables, if any, are present as well
    foreach($m2m_fields as $field) {
        // 4) verify that the DB table exists
        $table_name = $schema[$field]['rel_table'];

        if(!isset($tables[$table_name])) {
            $result[] = "ERROR - DB - Class $class: Relational table ($table_name) specified by field {$field} does not exist in database";
            $is_error = true;
        }
    }
}
// filter result
foreach($result as $index => $line) {
    if(strpos($line, 'ERROR') === 0 && !in_array($params['level'], ['*', 'error'])) {
        unset($result[$index]);
        continue;
    }
    if(strpos($line, 'WARN') === 0 && !in_array($params['level'], ['*', 'warn'])) {
        unset($result[$index]);
        continue;
    }
}
$result = array_values($result);

if(!count($result)) $result[] = "INFO - Nothing to report.";

// send json result
$context->httpResponse()
        ->body(['result' => $result])
        ->send();
    
// in case of error(s), force exiting with an error code		
if($is_error) {
    exit(1);
}




function view_test($data, $structure) {
    $sub_keys = array_keys($data);
    if(!is_numeric($sub_keys[0])) {
        $data = [$data];
    }    
    foreach($data as $elem) {            
        foreach($structure as $item => $def) {
            $key = is_numeric($item)?$def:$item;
            if(is_numeric($item)) {
                if(!isset($elem[$key])) {
                    return "missing mandatory property '$key'";
                }
            }
            else {                
                $res = view_test($elem[$key], $structure[$item]);
                if($res) {
                    return $res;
                }
            }
        }
    }
    return 0;
}

function view_get_items($data, $structure) {
    $result = [];
    $sub_keys = array_keys($data);
    if(!is_numeric($sub_keys[0])) {
        $data = [$data];
    }    
    foreach($data as $elem) {            
        foreach($structure as $item => $def) {
            if(!is_numeric($item)) {
                if($item == 'items') {
                    $result = array_merge($result, $elem[$item]);
                }
                else {
                    $res = view_get_items($elem[$item], $structure[$item]);
                    $result = array_merge($result, $res);
                }
            }
        }
    }
    return $result;
}