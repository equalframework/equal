<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnection;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

// announce script and fetch parameters values
list($params, $providers) = eQual::announce([
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
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];


/*
#TODO : check config and json files syntax.

* translation files constraints:
    * model helpers : max 45 chars
    * error messages length : max 45 chars

* for each view, check that each field is present 0 or 1 time
* for each view, check that the id match an entry in the translation file
* for each class, check that all fields are each field is present at least in one view

*/

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];
$is_error = false;

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);


/**
* TESTING FILES
*
*/
$lang_dir = "packages/{$params['package']}/i18n";
$view_dir = "packages/{$params['package']}/views";

$lang_list = [];
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
        throw new Exception("FATAL - class definition file missing for '{$class_name}' ", QN_ERROR_UNKNOWN_OBJECT);
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

    // verify that the class actually inherits from Model
    // retrieve root class (before Model)
    $root_parent = get_parent_class($model);
    while($root_parent && $root_parent != 'equal\orm\Model') {
        $root_parent = get_parent_class($root_parent);
    }

    if(!$root_parent ) {
        throw new Exception("FATAL - ORM - Class $class_name does not inherit from `equal\orm\Model` root class.", QN_ERROR_UNKNOWN_OBJECT);
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // 1) check fields descriptors consistency

    $valid_types = array_merge($orm::$virtual_types, $orm::$simple_types, $orm::$complex_types);

    // #toto - fields involved in unique constraint should be set as required

    foreach($schema as $field => $descriptor) {
        if(!isset($descriptor['type'])) {
            $result[] = "ERROR - ORM - Class $class: Missing 'type' attribute for field $field ($class_filename)";
            $is_error = true;
            continue;
        }
        if(!in_array($descriptor['type'], $valid_types)) {
            $result[] = "ERROR - ORM - Class $class: Invalid type '{$descriptor['type']}' for field $field ($class_filename)";
            $is_error = true;
            continue;
        }
        if(!$orm::checkFieldAttributes($orm::$mandatory_attributes, $schema, $field)) {
            $result[] = "ERROR - ORM - Class $class: Missing at least one mandatory attribute for field '$field' ({$descriptor['type']}) - mandatory attributes are : ".implode(', ', $orm::$mandatory_attributes[$descriptor['type']])." ($class_filename)";
            $is_error = true;
            continue;
        }
        foreach($descriptor as $attribute => $value) {
            if(!in_array($attribute, $orm::$valid_attributes[$descriptor['type']])) {
                $result[] = "ERROR - ORM - Class $class: Unknown attribute '$attribute' for field '$field' ({$descriptor['type']}) - Possible attributes are : ".implode(', ', $orm::$valid_attributes[$descriptor['type']])." ($class_filename)";
                $is_error = true;
            }
            if(in_array($attribute, array('store', 'multilang', 'readonly')) && $value !== true && $value !== false) {
                $result[] = "ERROR - ORM - Class $class: Incompatible value for attribute $attribute in field $field of type {$descriptor['type']} (possible attributes are : true, false)"." ($class_filename)";
                $is_error = true;
            }
            if($attribute == 'foreign_object' && !class_exists($value))  {
                $result[] = "ERROR - ORM - Class $class: Non-existing entity '{$value}' given for attribute $attribute in field $field of type {$descriptor['type']}"." ($class_filename)";
                $is_error = true;
            }
        }
    }

    $uniques = $model->getUnique();
    foreach($uniques as $unique_fields) {
        foreach($unique_fields as $ufield) {
            if(!isset($schema[$ufield])) {
                $result[] = "ERROR - ORM - Class $class: Non-existing field '{$ufield}' given in unique constraints";
                $is_error = true;
            }
        }
    }

    // #todo - 2) check presence of class definition files to which some field may be related to + domain validity (ref to other object), when present


    // 3) check if default views are present (form.default.json and list.default.json)

    try {
        eQual::run('get', 'model_view', ['entity' => $class_name, 'view_id' => 'form.default']);
    }
    catch(Exception $e) {
        $result[] = "ERROR - GUI - Class $class: missing default form view (/views/$class.form.default.json)";
    }

    try {
        eQual::run('get', 'model_view', ['entity' => $class_name, 'view_id' => 'list.default']);
    }
    catch(Exception $e) {
        $result[] = "ERROR - GUI - Class $class: missing default list view (/views/$class.list.default.json)";
    }

    // 4) check if translation file are present (.json)

    foreach($lang_list as $lang) {
        try {
            eQual::run('get', 'config_i18n', ['entity' => $class_name, 'lang' => $lang]);
        }
        catch(Exception $e) {
            $result[] = "WARN  - I18 - Class $class: missing translation file for language $lang";
        }
    }

    // 5) check view files consistency (.json)

    $view_files = glob("$view_dir/$class.*.json");

    foreach($view_files as $view_file) {
        $json = file_get_contents($view_file);
        $data = @json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $result[] = "ERROR - GUI - Syntax error in file: $view_file";
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
        else if(strpos($view_file, 'list.') > 0) {
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
        else if(strpos($view_file, 'chart.') > 0) {
            $structure = [
                'name',
                'description',
                'layout' => [
                    'entity',
                    'group_by',
                    'datasets' => [
                        'label', 'operation'
                    ]
                ]
            ];
        }
        // check that mandatory properties are present in the view
        $res = view_test($data, $structure);
        if($res) {
            $result[] = "ERROR - GUI - ".$res." in file: $view_file";
            $is_error = true;
        }
        // check that fields targeted in views are valid (defined in schema)
        $items = view_get_items($data, $structure);
        foreach($items as $item) {
            if(isset($item['type']) && $item['type'] == 'field' && isset($item['value'])) {
                $field = $item['value'];
                if(!isset($schema[$field])) {
                    $result[] = "ERROR - GUI - Unknown field '$field' referenced in file $view_file";
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
                $result[] = "ERROR - I18 - Syntax error in file: $i18n_file";
                $is_error = true;
                continue;
            }
            $mandatory_properties = ['name', 'plural', 'description', 'model', 'view'];
            foreach($mandatory_properties as $property) {
                if(!isset($data[$property])) {
                    $result[] = "ERROR - I18 - Missing mandatory property '$property' in file: $i18n_file";
                    $is_error = true;
                }
            }
            if(isset($data['model'])) {
                $fields = array_keys($data['model']);
                // check that the referenced fields are valid (defined in the schema)
                foreach($fields as $field) {
                    if(!isset($schema[$field])) {
                        $result[] = "WARN  - I18 - Unknown field '$field' referenced in file $i18n_file";
                    }
                    // warn about renaming root fields (special fields from Model interface)
                    if(in_array($field, ['id', 'creator', 'modifier', 'modified','created', 'deleted', 'state'])) {
                        $result[] = "WARN  - I18 - Root field '$field' shouldn't be referenced in file $i18n_file";
                    }
                }
                // check that the translation description is complete for each field
                foreach($fields as $field) {
                    $mandatory_properties = ['label', 'help', 'description'];
                    foreach($mandatory_properties as $property) {
                        if(!isset($data['model'][$field][$property])) {
                            $result[] = "WARN  - I18 - Missing property '$property' for field '$field' referenced in file $i18n_file";
                        }
                        else {
                            if(mb_strlen($data['model'][$field][$property]) == 0) {
                                $result[] = "WARN  - I18 - Value for property '$property' should not be empty for field '$field' referenced in file $i18n_file";
                                continue;
                            }
                            if(!preg_match('/^\p{Lu}/u', $data['model'][$field][$property])) {
                                $result[] = "WARN  - I18 - Value for property '$property' should start with uppercase for field '$field' referenced in file $i18n_file";
                            }
                            if($property == 'label') {
                                if( mb_strlen($data['model'][$field][$property]) && substr($data['model'][$field][$property], -1) == '.' ) {
                                    $result[] = "WARN  - I18 - Value for property '$property' should not end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                            else if($property == 'help') {
                                if( mb_strlen($data['model'][$field][$property]) && !in_array(substr($data['model'][$field][$property], -1), ['.', '?', '!']) ) {
                                    $result[] = "WARN  - I18 - Value for property '$property' should end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                            else if($property == 'description') {
                                if( mb_strlen($data['model'][$field][$property]) ) {
                                    if( !in_array(substr($data['model'][$field][$property], -1), ['.', '?', '!']) ) {
                                        $result[] = "WARN  - I18 - Value for property '$property' should end by '.' for field '$field' referenced in file $i18n_file";
                                    }
                                    if( mb_strlen($data['model'][$field][$property]) > 65) {
                                        $result[] = "WARN  - I18 - Property '$property' should not exceed 65 chars for field '$field' referenced in file $i18n_file";
                                    }
                                }
                            }
                        }
                    }
                    // check for 'selection' property
                    if($schema[$field]['type'] == 'string' && isset($schema[$field]['selection'])) {
                        if(!isset($data['model'][$field]['selection'])) {
                            $result[] = "WARN  - I18 - Missing property 'selection' for field '$field' referenced in file $i18n_file";
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


// retrieve connection object
$db = DBConnection::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();

if(!$db) {
    throw new Exception('missing_database', QN_ERROR_INVALID_CONFIG);
}

// load database tables
$tables_map = array_fill_keys($db->getTables(), true);

$allowed_types_associations = [
    'boolean' 		=> array('bool', 'bit', 'tinyint', 'smallint', 'mediumint', 'int', 'bigint'),
    'integer' 		=> array('tinyint', 'smallint', 'mediumint', 'int', 'bigint'),
    'float' 		=> array('float', 'decimal', 'real'),
    'string' 		=> array('char', 'varchar', 'nvarchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'blob', 'mediumblob'),
    'text' 			=> array('nvarchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'blob'),
    'html' 			=> array('tinytext', 'text', 'mediumtext', 'longtext', 'blob'),
    'date' 			=> array('date', 'datetime'),
    'time' 			=> array('time'),
    'datetime' 		=> array('datetime', 'datetime2'),
    'file'  		=> array('blob', 'mediumblob', 'longblob'),
    'binary' 		=> array('blob', 'mediumblob', 'longblob'),
    'many2one' 		=> array('int')
];

foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;

    $model = $orm->getModel($entity);
    if(!is_object($model)) {
        throw new Exception("unknown class '{$entity}'", QN_ERROR_UNKNOWN_OBJECT);
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();
    $class_filename = str_replace('\\', '/', "packages/{$params['package']}/classes/{$class}".'.class.php');

    // get the SQL table name
    $table = $orm->getObjectTableName($entity);

    // 1) verify that the DB table exists
    if(!isset($tables_map[$table])) {
        $result[] = "ERROR - DBM - Class $class: Associated table ({$table}) does not exist in database ($class_filename)";
        $is_error = true;
        continue;
    }

    // load DB schema
    $db_schema = $db->getTableSchema($table);

    $simple_fields = [];
    $m2m_fields = [];
    foreach($schema as $field => $descriptor) {
        if(in_array($descriptor['type'], $orm::$simple_types)) {
            $simple_fields[] = $field;
        }
        // handle the 'store' attribute
        elseif(in_array($descriptor['type'], array('computed', 'related'))) {
            if(isset($descriptor['store']) && $descriptor['store']) {
                $simple_fields[] = $field;
            }
        }
        elseif($descriptor['type'] == 'many2many') {
            $m2m_fields[] = $field;
        }

        if(isset($descriptor['onupdate'])) {
            $parts = explode('::', $descriptor['onupdate']);
            $count = count($parts);

            $called_class = $entity;
            $called_method = $descriptor['onupdate'];

            if( $count < 1 || $count > 2 ) {
                $result[] = "ERROR - ORM - Class $entity: Field $field has invalid onupdate property ({$descriptor['onupdate']})";
            }
            else {
                if( $count == 2 ) {
                    $called_class = $parts[0];
                    $called_method = $parts[1];
                }
                if(!method_exists($called_class, $called_method)) {
                    $result[] = "ERROR - ORM - Class $entity: Field $field has onupdate property with unknown handler '{$descriptor['onupdate']}'";
                }
            }
        }

        if(isset($descriptor['description'])) {
            if( mb_strlen($descriptor['description']) ) {
                if(!preg_match('/^\p{Lu}/u', $descriptor['description'])) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should start with uppercase for field '$field' referenced in file $class_filename";
                }
                if(!in_array(substr($descriptor['description'], -1), ['.', '?', '!'])) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should end by '.' for field '$field' referenced in file $class_filename";
                }
                if( mb_strlen($descriptor['description']) > 65) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should not exceed 65 chars for field '$field' referenced in file $class_filename";
                }
            }
        }
    }
    // a) check that every declared simple field is present in the associated DB table
    foreach($simple_fields as $field) {
        // 2) verify that the fields exists in DB
        if(!isset($db_schema[$field])) {
            $result[] = "ERROR - DBM - Class $class: Field $field ({$schema[$field]['type']}) does not exist in table {$table} ($class_filename)";
            $is_error = true;
        }
        else {
        // 3) verify types compatibility
            $type = $schema[$field]['type'];
            if(in_array($type, array('computed', 'related'))) $type = $schema[$field]['result_type'];
            if(!in_array($db_schema[$field]['type'], $allowed_types_associations[$type])) {
                $result[] = "ERROR - DBM - Class $class: Non compatible type in database ({$db_schema[$field]['type']}) for field $field ({$schema[$field]['type']}) ($class_filename)";
                $is_error = true;
            }
        }
    }
    // b) check that relational tables, if any, are present as well
    foreach($m2m_fields as $field) {
        // 4) verify that the DB table exists
        $table_name = $schema[$field]['rel_table'];

        if(!isset($tables_map[$table_name])) {
            $result[] = "ERROR - DBM - Class $class: Relational table ($table_name) specified by field {$field} does not exist in database ($class_filename)";
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

if(!count($result)) {
    $result[] = "INFO - Nothing to report.";
}

// send json result
$context->httpResponse()
        ->body(['result' => $result])
        ->send();

// in case of error(s), force exiting with an error code
if($is_error) {
    exit(1);
}





/**
 * Utility functions (hoisted)
 */



function view_test($data, $structure) {
    $sub_keys = array_keys($data);
    if(!is_numeric($sub_keys[0])) {
        $data = [$data];
    }
    foreach($data as $index => $elem) {
        foreach($structure as $item => $def) {
            $key = is_numeric($item)?$def:$item;
            if(is_numeric($item)) {
                if(!isset($elem[$key])) {
                    return "missing property '$key' for item $index";
                }
            }
            else if(isset($elem[$key]) && isset($structure[$item])){
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