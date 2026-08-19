<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\db\DBConnector;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'type'          =>  'do',
    'name'          =>  'test_package_db-consistency',
    'package_name'  =>  'core',
    'description'   =>  'Test database consistency for package model definitions.',
    'params'        =>  [
        'package'   =>  [
            'description'   => 'Package to validate.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'level' =>  [
            'description'   => 'Level of notification to limit the output to.',
            'type'          => 'string',
            'selection'     => ['*', 'warn', 'error'],
            'default'       => '*'
        ],
        'strict'    =>  [
            'description'   => 'Flag to enable strict mode.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'exit_on_error' => [
            'description'   => 'Exit with a non-zero status code when consistency errors are found.',
            'type'          => 'boolean',
            'default'       => true
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
[$context, $orm] = [$providers['context'], $providers['orm']];

$filter_result = function(array $result, string $level): array {
    foreach($result as $index => $line) {
        if(strpos($line, 'ERROR') === 0 && !in_array($level, ['*', 'error'])) {
            unset($result[$index]);
            continue;
        }
        if(strpos($line, 'WARN') === 0 && !in_array($level, ['*', 'warn'])) {
            unset($result[$index]);
            continue;
        }
    }
    return array_values($result);
};

$count_result = function(array $result): array {
    $counts = ['errors' => 0, 'warnings' => 0];
    foreach($result as $line) {
        if(strpos($line, 'ERROR') === 0) {
            ++$counts['errors'];
        }
        elseif(strpos($line, 'WARN') === 0) {
            ++$counts['warnings'];
        }
    }
    return $counts;
};

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

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
$db = DBConnector::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();

if(!$db) {
    throw new Exception('missing_database', QN_ERROR_INVALID_CONFIG);
}

// load database tables
$tables_map = array_fill_keys($db->getTables(), true);

$allowed_types_associations = [
    'boolean'       => ['bool', 'bit', 'tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
    'integer'       => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
    'float'         => ['float', 'decimal', 'real'],
    'string'        => ['char', 'varchar', 'nvarchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'blob', 'mediumblob'],
    'text'          => ['nvarchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'blob'],
    'html'          => ['tinytext', 'text', 'mediumtext', 'longtext', 'blob'],
    'date'          => ['date', 'datetime'],
    'time'          => ['time'],
    'datetime'      => ['datetime', 'datetime2'],
    'file'          => ['blob', 'mediumblob', 'longblob'],
    'binary'        => ['blob', 'mediumblob', 'longblob'],
    'many2one'      => ['int']
];

foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;

    if(!class_exists($entity) || !is_subclass_of($entity, 'equal\orm\Model')) {
        continue;
    }

    $model = $orm->getModel($entity);
    if(!is_object($model)) {
        $result[] = "ERROR - DBM - Class $class: unable to load model for '{$entity}'";
        continue;
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();
    $class_filename = str_replace('\\', '/', "packages/{$params['package']}/classes/{$class}".'.class.php');

    // get the SQL table name
    $table = $orm->getObjectTableName($entity);

    // 1) verify that the DB table exists
    if(!isset($tables_map[$table])) {
        $result[] = "ERROR - DBM - Class $class: Associated table ({$table}) does not exist in database ($class_filename)";
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
        elseif(in_array($descriptor['type'], ['computed', 'related'])) {
            if(isset($descriptor['store']) && $descriptor['store']) {
                $simple_fields[] = $field;
            }
        }
        elseif($descriptor['type'] == 'many2many') {
            $m2m_fields[] = $field;
        }
    }
    // a) check that every declared simple field is present in the associated DB table
    foreach($simple_fields as $field) {
        // 2) verify that the fields exists in DB
        if(!isset($db_schema[$field])) {
            $result[] = "ERROR - DBM - Class $class: Field $field ({$schema[$field]['type']}) does not exist in table {$table} ($class_filename)";
        }
        else {
            // 3) verify types compatibility
            $type = $schema[$field]['type'];
            if(in_array($type, ['computed', 'related'])) {
                $type = $schema[$field]['result_type'];
            }
            if(!in_array($db_schema[$field]['type'], $allowed_types_associations[$type])) {
                $result[] = "ERROR - DBM - Class $class: Non compatible type in database ({$db_schema[$field]['type']}) for field $field ({$schema[$field]['type']}) ($class_filename)";
            }
        }
    }
    // b) check that relational tables, if any, are present as well
    foreach($m2m_fields as $field) {
        // 4) verify that the DB table exists
        $table_name = $schema[$field]['rel_table'];

        if(!isset($tables_map[$table_name])) {
            $result[] = "ERROR - DBM - Class $class: Relational table ($table_name) specified by field {$field} does not exist in database ($class_filename)";
        }
    }
}

// filter result
$counts = $count_result($result);
$result = $filter_result($result, $params['level']);

if(!count($result)) {
    $result[] = 'INFO - Nothing to report.';
}

// send json result
$context->httpResponse()
        ->body([
            'result'   => $result,
            'errors'   => $counts['errors'],
            'warnings' => $counts['warnings']
        ])
        ->send();

// in case of error(s), force exiting with an error code
if($counts['errors'] && $params['exit_on_error']) {
    exit(1);
}
