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
    'description'	=> "Returns the schema of the specified package in standard SQL ('CREATE' statements with 'IF NOT EXISTS' clauses).",
    'params'        => [
        'package'   => [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'full'	=> [
            'description'   => 'Force the output to complete schema (i.e. with tables already present in DB).',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
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
$db = DBConnection::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();


$result = array();
$m2m_tables = array();

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

// associative array with 2 levels, mapping tables with their list of columns
$processed_columns = [];

// remember tables used by inherited classes (can only be overriden by children, not by ancestor)
$parent_tables = [];

foreach($classes as $class) {
    // get the full class name
    $class_name = $params['package'].'\\'.$class;
    $model = $orm->getModel($class_name);
    if(!is_object($model)) throw new Exception("unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);

    // get the SQL table name
    $table = $orm->getObjectTableName($class_name);
    $direct_name = strtolower(str_replace('\\', '_', $class_name));
    // handle inherited classes
    if($table != $direct_name) {
        // table name is the table of an ancestor
        $parent_tables[] = $table;
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    if(!isset($processed_columns[$table])) {
        $processed_columns[$table] = [];
    }

    // #memo - deleting tables prevents keeping data across inherited classes
    // $result[] = "DROP TABLE IF EXISTS `{$table}`;";

    // fetch existing column
    $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA='".constant('DB_NAME')."';";
    $res = $db->sendQuery($query);

    $columns = [];
    while ($row = $db->fetchArray($res)) {
        $columns[] = $row['COLUMN_NAME'];
    }

    // if some columns already exist (we are enriching a table related to a class from which the current class inherits),
    // then we append only the columns that do not exit yet
    if(!empty($columns) && !$params['full']) {
        $columns_diff = array_diff(array_keys($schema), $columns);
        foreach($columns_diff as $field) {
            // prevent processing a same column more than once
            if(isset($processed_columns[$table][$field])) {
                continue;
            }
            $description = $schema[$field];
            if(in_array($description['type'], array_keys(ObjectManager::$types_associations))) {
                $type = ObjectManager::$types_associations[$description['type']];
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
                if($field == 'id') {
                    $result[] = "ALTER TABLE `{$table}` ADD `{$field}` {$type} NOT NULL AUTO_INCREMENT;";
                }
                elseif(in_array($field, array('creator','modifier','published','deleted'))) {
                    $result[] = "ALTER TABLE `{$table}` ADD `{$field}` {$type} NOT NULL DEFAULT '0';";
                }
                else {
                    $result[] = "ALTER TABLE `{$table}` ADD `{$field}` {$type};";
                }
            }
            elseif( $description['type'] == 'computed' && isset($description['store']) && $description['store'] ) {
                $type = ObjectManager::$types_associations[$description['result_type']];
                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }
                $result[] = "ALTER TABLE `{$table}` ADD `{$field}` {$type} DEFAULT NULL;";
            }
            elseif($description['type'] == 'many2many') {
                if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
            $processed_columns[$table][$field] = true;
        }
    }
    // if no columns were found, the table is either empty or do not exist yet
    // in this case, we create the table and add all the columns
    else {
        $queries = [];
        $count_fields = 0;
        $queries[] = "CREATE TABLE IF NOT EXISTS `{$table}` (";
        if(!isset($processed_columns[$table])) {
            $processed_columns[$table] = [];
        }
        foreach($schema as $field => $description) {
            // prevent processing a same column more than once
            if(isset($processed_columns[$table][$field])) {
                continue;
            }
            if(in_array($description['type'], array_keys(ObjectManager::$types_associations))) {
                $type = ObjectManager::$types_associations[$description['type']];

                // if a SQL type is associated to field 'usage', it prevails over the type association
                if( isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']]) ) {
                    $type = ObjectManager::$usages_associations[$description['usage']];
                }

                if($field == 'id') {
                    $queries[] = "`{$field}` {$type} NOT NULL AUTO_INCREMENT,";
                }
                elseif(in_array($field, array('creator','modifier','published','deleted'))) {
                    $queries[] = "`{$field}` {$type} NOT NULL DEFAULT '0',";
                }
                else {
                    $queries[] = "`{$field}` {$type} DEFAULT NULL,";
                }
            }
            elseif( $description['type'] == 'computed' && isset($description['store']) && $description['store'] ) {
                $type = ObjectManager::$types_associations[$description['result_type']];
                $queries[] = "`{$field}` {$type} DEFAULT NULL,";
            }
            elseif($description['type'] == 'many2many') {
                if(!isset($m2m_tables[$description['rel_table']])) $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
            $processed_columns[$table][$field] = true;
            ++$count_fields;
        }
        $queries[] = "PRIMARY KEY (`id`)";

        if(method_exists($model, 'getUnique')) {
            $list = $model->getUnique();
            foreach($list as $unique) {
                $parts = [];
                foreach($unique as $field) {
                    $part = "`{$field}`";
                    // limit index size to 20 bytes for strings
                    if(in_array($schema[$field]['type'], ['string', 'text'])) {
                        $part .= "(25)";
                    }
                    $parts[] = $part;
                }
                // #todo - deprecate
                // #memo - Classes are allowed to override the getUnique method. Therefore, we cannot apply parent unicity constraints to parent table (which also applies on all inherited classes)
                // $result[] = ",UNIQUE KEY `".implode('_', $unique)."` (".implode(',', $parts).")";
            }
        }

        $queries[] = ") DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;\n";
        if($count_fields > 0) {
            foreach($queries as $query) {
                $result[] = $query;
            }
        }

    }

}

foreach($m2m_tables as $table => $columns) {
    if(!isset($processed_columns[$table])) {
        $processed_columns[$table] = [];
    }
    $queries = [];
    $queries[] = "CREATE TABLE IF NOT EXISTS `{$table}` (";
    $key = '';
    $count_fields = 0;
    foreach($columns as $column) {
        // prevent processing a same column more than once
        if(isset($processed_columns[$table][$column])) {
            continue;
        }
        $queries[] = "`{$column}` int(11) NOT NULL,";
        $key .= "`$column`,";
        $processed_columns[$table][$column] = true;
        ++$count_fields;
    }
    $key = rtrim($key, ",");
    $queries[] = "PRIMARY KEY ({$key})";
    $queries[] = ");";
    if($count_fields > 0) {
        foreach($queries as $query) {
            $result[] = $query;
        }
    }
    // add an empty record (required for JOIN conditions on empty tables)
    $result[] = "INSERT IGNORE INTO `{$table}` (".implode(',', array_map(function($col) {return "`{$col}`";}, $columns)).') VALUES ';
    $result[] = '('.implode(',', array_fill(0, count($columns), 0)).");";
}

// send json result
$context->httpResponse()
        ->body(['result' => implode("\n", $result)])
        ->send();
