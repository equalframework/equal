<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\db\DBConnector;
use equal\data\adapt\DataAdapterProviderSql;

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

list($params, $providers) = eQual::announce([
    'description'	=> "Returns the schema of the specified package in standard SQL ('CREATE' statements with 'IF NOT EXISTS' clauses).",
    'params'        => [
        'package'   => [
            'description'   => 'Package for which we want SQL schema.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'full'	=> [
            'description'   => 'Force the output to complete schema (i.e. all tables with all columns, even if already present in DB).',
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

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

eQual::run('do', 'test_db-access');

// retrieve connection object
$db = DBConnector::getInstance(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_DBMS'))->connect();

if(!$db) {
    throw new Exception('missing_database', EQ_ERROR_INVALID_CONFIG);
}

$dap = new DataAdapterProviderSql();

$result = [];
$m2m_tables = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

// associative array with 2 levels, mapping tables with their list of columns
$processed_columns = [];

foreach($classes as $class) {
    // get the full class name
    $entity = $params['package'].'\\'.$class;
    // retrieve the static instance of the entity
    $model = $orm->getModel($entity);

    if(!is_object($model)) {
        throw new Exception("unknown class '{$entity}'", EQ_ERROR_UNKNOWN_OBJECT);
    }

    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // get the SQL table name
    $table = $orm->getObjectTableName($entity);

    if(!isset($processed_columns[$table])) {
        $processed_columns[$table] = [];
    }

    // #memo - we cannot delete tables since it prevents keeping data across inherited classes

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

        $processed_columns[$table][$field] = true;

        $f = $model->getField($field);
        $descriptor = $f->getDescriptor();

        if($descriptor['type'] == 'one2many') {
            continue;
        }
        elseif($descriptor['type'] == 'many2many') {
            if(!isset($m2m_tables[$descriptor['rel_table']])) {
                $m2m_tables[$descriptor['rel_table']] = [ $descriptor['rel_foreign_key'],  $descriptor['rel_local_key'] ];
            }
            continue;
        }
        elseif($descriptor['type'] == 'computed') {
            if(!isset($descriptor['store']) || !$descriptor['store']) {
                // skip non-stored computed fields
                continue;
            }
        }

        $adapter = $dap->get($f->getContentType());
        if(!$adapter) {
            throw new Exception('unresolved_adapter', EQ_ERROR_INVALID_CONFIG);
        }

        $type = $adapter->castOutType($f->getUsage());
        if(!strlen($type)) {
            trigger_error("ORM::unresolved type for usage {$usage->getName()}", EQ_REPORT_DEBUG);
            throw new Exception('unresolved_sql_type', EQ_ERROR_INVALID_CONFIG);
        }

        $column_descriptor = [
                'type'      => $type,
                'null'      => true
            ];

        // #todo - if a SQL type is associated to field 'usage', it prevails over the type association
        if(isset($descriptor['usage']) && isset(ObjectManager::$usages_associations[$descriptor['usage']])) {
            // $type = ObjectManager::$usages_associations[$descriptor['usage']];
        }

        if($field == 'id') {
            continue;
            // #memo - id column is added at table creation (auto_increment + primary key)
        }
        elseif(in_array($field, array('creator','modifier'))) {
            $column_descriptor['null'] = false;
        }
        // generate SQL for column creation
        $result[] = $db->getQueryAddColumn($table, $field, $column_descriptor);

        // #memo - default is supported and handled by the ORM, not by the DBMS
        // if table already exists, set column value according to default, for all existing records
        if(count($columns) && isset($descriptor['default'])) {
            // #todo - computed defaults are not supported for existing objects
            $default = null;
            if(is_callable($descriptor['default'])) {
                // either a php function (or a function from the global scope) or a closure object
                if(is_object($descriptor['default'])) {
                    // default is a closure
                    $default = $descriptor['default']();
                }
            }
            elseif(!is_string($descriptor['default']) || !method_exists($model->getType(), $descriptor['default'])) {
                // default is a scalar value
                $default = $descriptor['default'];
            }
            $result[] = $db->getQuerySetRecords($table, [$field => $default]);
        }
    }

    if(method_exists($model, 'getUnique')) {
        // #memo - Classes are allowed to override the getUnique method from their parent class. Unique checks are performed by ORM.
        // Therefore we cannot apply parent uniqueness constraints on parent table since it would also applies on all inherited classes.
        // However, even if check is made by ORM, each column member of a unique tuple must be indexed (for performance concerns).
        $constraints = (array) $model->getUnique();
        $map_index_fields = [];
        foreach($constraints as $uniques) {
            foreach((array) $uniques as $unique_field) {
                if(isset($schema[$unique_field])) {
                    $map_index_fields[$unique_field] = true;
                }
            }
        }
        foreach($map_index_fields as $unique_field => $flag) {
            // create an index for fields not yet present in DB
            if(!in_array($unique_field, $columns)) {
                $result[] = $db->getQueryAddIndex($table, $unique_field);
            }
        }
    }
}

foreach($m2m_tables as $table => $columns) {
    if(!isset($processed_columns[$table])) {
        $processed_columns[$table] = [];
    }
    // fetch existing columns
    $existing_columns = $db->getTableColumns($table);
    // create table if not exist
    $result[] = $db->getQueryCreateTable($table);
    if(!$params['full'] && count($existing_columns) && count(array_diff($columns, $existing_columns)) <= 0) {
        continue;
    }
    foreach($columns as $column) {
        if(in_array($column, $existing_columns)) {
            continue;
        }
        if(isset($processed_columns[$table][$column])) {
            continue;
        }

        $adapter = $dap->get('number/natural');

        $result[] = $db->getQueryAddColumn($table, $column, [
            'type'      => $adapter->castOutType(),
            'null'      => false
        ]);
        $processed_columns[$table][$column] = true;
    }
    $result[] = $db->getQueryAddUniqueConstraint($table, $columns);
    // add an empty record (required for JOIN conditions on empty tables)
    $result[] = $db->getQueryAddRecords($table, $columns, [array_fill(0, count($columns), 0)]);
}

// provide SQL schema as a JSON encoded SQL query
$context->httpResponse()
        ->body(['result' => implode("\n", $result)])
        ->send();
