<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'type'          => 'do',
    'name'          => 'model-inspect',
    'package_name'  => 'core',
    'description'   => 'Check that every database table associated with a configured ORM model and each of its records define a model discriminator.',
    'params'        => [
        'exit_on_error' => [
            'description'   => 'Exit with a non-zero status code when inconsistencies are found.',
            'type'          => 'boolean',
            'default'       => true
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'db'],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ]
]);

/**
 * @var \equal\php\Context       $context
 * @var \equal\orm\ObjectManager $orm
 * @var \equal\db\DBConnector    $dbConnector
 */
['context' => $context, 'orm' => $orm, 'db' => $dbConnector] = $providers;

eQual::run('do', 'test_db-access');

$db = $dbConnector->connect();

if(!$db) {
    throw new Exception('missing_database', EQ_ERROR_INVALID_CONFIG);
}

$quoteIdentifier = static function(string $identifier): string {
    $parts = explode('.', $identifier);
    foreach($parts as &$part) {
        if(constant('DB_DBMS') === 'SQLSRV') {
            $part = '['.str_replace(']', ']]', $part).']';
        }
        else {
            $part = '`'.str_replace('`', '``', $part).'`';
        }
    }
    unset($part);

    return implode('.', $parts);
};

$discoverTables = static function($orm): array {
    $model_tables = [];
    $m2m_tables = [];
    $packages = eQual::run('get', 'config_packages');

    foreach($packages as $package) {
        $classes = eQual::run('get', 'config_classes', ['package' => $package]);
        foreach($classes as $class) {
            $entity = $package.'\\'.$class;
            try {
                $model = $orm->getModel($entity);
            }
            catch(Exception $e) {
                // Configured class files are not necessarily ORM models.
                continue;
            }
            if(!is_object($model) || !is_a($model, \equal\orm\Model::class)) {
                continue;
            }

            $type = $model->getType();
            $table = $orm->getObjectTableName($type);
            if(!is_string($table) || !strlen($table)) {
                throw new Exception('unresolved_model_table', EQ_ERROR_INVALID_CONFIG);
            }
            $model_tables[$table] = true;

            foreach($model->getSchema() as $descriptor) {
                if(($descriptor['type'] ?? null) !== 'many2many') {
                    continue;
                }
                $table = $descriptor['rel_table'] ?? null;
                if(is_string($table) && strlen($table)) {
                    $m2m_tables[$table] = true;
                }
            }
        }
    }

    ksort($model_tables);
    ksort($m2m_tables);

    return [$model_tables, $m2m_tables];
};

$countRows = static function($db, string $table) use($quoteIdentifier): int {
    $result = $db->sendQuery('SELECT COUNT(*) AS row_count FROM '.$quoteIdentifier($table));
    $row = $db->fetchArray($result);
    if(!is_array($row) || !array_key_exists('row_count', $row)) {
        throw new Exception('unresolved_table_row_count', EQ_ERROR_UNKNOWN);
    }

    return (int) $row['row_count'];
};

$inspectModelValues = static function($db, string $table) use($quoteIdentifier): array {
    $row_count = 0;
    $missing_model_values = 0;
    $result = $db->sendQuery(
        'SELECT '.$quoteIdentifier('model').' FROM '.$quoteIdentifier($table)
    );

    while($row = $db->fetchArray($result)) {
        ++$row_count;
        $value = $row['model'] ?? null;
        if(is_null($value) || (is_string($value) && !strlen(trim($value)))) {
            ++$missing_model_values;
        }
    }

    return [$row_count, $missing_model_values];
};

$database_tables = array_fill_keys($db->getTables(), true);
ksort($database_tables);

[$model_tables, $m2m_tables] = $discoverTables($orm);
$model_tables = array_intersect_key($model_tables, $database_tables);
$skipped_m2m_tables = array_keys(array_intersect_key($m2m_tables, $database_tables));
$table_results = [];
$checked_tables = 0;
$tables_without_model_column = 0;
$records_without_model_value = 0;

foreach($model_tables as $table => $unused) {
    ++$checked_tables;
    $columns = $db->getTableColumns($table);
    $has_model_column = in_array('model', $columns, true);
    $row_count = 0;
    $missing_model_values = null;

    if(!$has_model_column) {
        ++$tables_without_model_column;
        $row_count = $countRows($db, $table);
    }
    else {
        [$row_count, $missing_model_values] = $inspectModelValues($db, $table);
        $records_without_model_value += $missing_model_values;
    }

    if(!$has_model_column || $missing_model_values > 0) {
        $table_results[] = [
            'table'                => $table,
            'row_count'            => $row_count,
            'has_model_column'     => $has_model_column,
            'missing_model_values' => $missing_model_values,
            'valid'                => false
        ];
    }
}

$valid = $tables_without_model_column === 0 && $records_without_model_value === 0;

$context->httpResponse()
    ->status(200)
    ->body([
        'valid'                       => $valid,
        'database_tables'             => count($database_tables),
        'checked_tables'              => $checked_tables,
        'skipped_m2m_tables'          => $skipped_m2m_tables,
        'tables_without_model_column' => $tables_without_model_column,
        'records_without_model_value' => $records_without_model_value,
        'tables'                      => $table_results
    ])
    ->send();

if(!$valid && $params['exit_on_error']) {
    exit(1);
}
