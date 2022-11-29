<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnection;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

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
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapt) = [$providers['context'], $providers['orm'], $providers['adapt']];

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

if(!$db) {
    throw new Exception('missing_database', QN_ERROR_INVALID_CONFIG);
}

$db_class = get_class($db);
$result = [];
$m2m_tables = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

// associative array with 2 levels, mapping tables with their list of columns
$processed_columns = [];

foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;
    // retrieve the static instance of the entityt
    $model = $orm->getModel($entity);

    if(!is_object($model)) {
        throw new Exception("unknown class '{$entity}'", QN_ERROR_UNKNOWN_OBJECT);
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // get the SQL table name
    $table = $orm->getObjectTableName($entity);

    if(!isset($processed_columns[$table])) {
        $processed_columns[$table] = [];
    }

    // #memo - deleting tables prevents keeping data across inherited classes
    // $result[] = "DROP TABLE IF EXISTS `{$table_name}`;";

    // fetch existing column
    $columns = $db->getTableColumns($table);

    // if some columns already exist (we are enriching a table related to a class from which the current class inherits),
    // then we append only the columns that do not exit yet
    $result[] = $db->getQueryCreateTable($table);

    // retrieve list of fields that must be added to the schema
    $columns_diff = ($params['full'])?array_keys($schema):array_diff(array_keys($schema), $columns);

    foreach($columns_diff as $field) {
        // prevent processing a same column more than once
        if(isset($processed_columns[$table][$field])) {
            continue;
        }
        $description = $schema[$field];
        if(in_array($description['type'], array_keys($db_class::$types_associations))) {
            $type = $db->getSqlType($description['type']);

            $column_descriptor = [
                    'type'      => $type,
                    'null'      => true
                ];

            // if a SQL type is associated to field 'usage', it prevails over the type association
            // #todo
            if(isset($description['usage']) && isset(ObjectManager::$usages_associations[$description['usage']])) {
                // $type = ObjectManager::$usages_associations[$description['usage']];
            }

            /*
            // #memo - default is supported by ORM, not DBMS
            if(isset($description['default']) && !is_callable($description['default'])) {
                $column_descriptor['default'] = $adapt->adapt($description['default'], $type, 'sql', 'php');
            }
            */

            if($field == 'id') {
                continue;
                // #memo - id column is added at table creation (auto_increment + primary key)
            }
            elseif(in_array($field, array('creator','modifier'))) {
                $column_descriptor['null'] = false;
            }
            // generate SQL for column creation
            $result[] = $db->getQueryAddColumn($table, $field, $column_descriptor);
        }
        elseif($description['type'] == 'computed') {
            if(!isset($description['store']) || !$description['store']) {
                // skip non-stored computed fields
                continue;
            }
            $result[] = $db->getQueryAddColumn($table, $field, [
                    'type'      => $db->getSqlType($description['result_type']),
                    'null'      => true,
                    'default'   => null
                ]);
        }
        elseif($description['type'] == 'many2many') {
            if(!isset($m2m_tables[$description['rel_table']])) {
                $m2m_tables[$description['rel_table']] = array($description['rel_foreign_key'], $description['rel_local_key']);
            }
        }
        $processed_columns[$table][$field] = true;
    }

    if(method_exists($model, 'getUnique')) {
        // #memo - Classes are allowed to override the getUnique method from their parent class.
        // Therefore, we cannot apply parent unicity constraints on parent table since it would also applies on all inherited classes.
    }

}

foreach($m2m_tables as $table => $columns) {
    $constraint_name = implode('_', $columns);
    $existing_constraints = $db->getTableConstraints($table);
    if(in_array($constraint_name, $existing_constraints)) {
        continue;
    }
    // fetch existing columns
    $existing_columns = $db->getTableColumns($table);
    // create table if not exist
    $result[] = $db->getQueryCreateTable($table);
    foreach($columns as $column) {
        if(in_array($column, $existing_columns)) {
            continue;
        }
        $type = $db->getSqlType('integer');
        $result[] = $db->getQueryAddColumn($table, $column, [
            'type'      => $type,
            'null'      => false
        ]);
    }
    $result[] = $db->getQueryAddConstraint($table, $columns);
    // add an empty record (required for JOIN conditions on empty tables)
    $result[] = $db->getQueryAddRecords($table, $columns, [array_fill(0, count($columns), 0)]);
}

// send json result
$context->httpResponse()
        ->body(['result' => implode("\n", $result)])
        ->send();
