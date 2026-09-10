<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\adapt\DataAdapterProviderSql;
use equal\db\DBConnector;

[$params, $providers] = eQual::announce([
    'description'   => 'Discover ORM model-table mappings, then validate an edited migration file and backfill model discriminators.',
    'params'        => [
        'phase' => [
            'description'   => 'Migration phase to execute.',
            'type'          => 'string',
            'selection'     => ['discover', 'backfill'],
            'required'      => true
        ]
    ],
    'constants'     => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm'],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ]
]);

/**
 * @var \equal\php\Context       $context
 * @var \equal\orm\ObjectManager $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

eQual::run('do', 'test_db-access');

$db = DBConnector::getInstance(
    constant('DB_HOST'),
    constant('DB_PORT'),
    constant('DB_NAME'),
    constant('DB_USER'),
    constant('DB_PASSWORD'),
    constant('DB_DBMS')
)->connect();

if(!$db) {
    throw new Exception('missing_database', EQ_ERROR_INVALID_CONFIG);
}

$migration_file = EQ_BASEDIR.'/cache/model-migration.json';

$normalizeValue = static function($value): string {
    if(is_null($value)) {
        return 'null:';
    }
    if(is_bool($value)) {
        return 'scalar:'.($value ? '1' : '0');
    }
    return 'scalar:'.(string) $value;
};

$countTableRows = static function($db, string $table): int {
    $quote = static function(string $identifier): string {
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

    $result = $db->sendQuery('SELECT COUNT(*) AS row_count FROM '.$quote($table));
    $row = $db->fetchArray($result);
    if(!is_array($row) || !array_key_exists('row_count', $row)) {
        throw new Exception('unresolved_table_row_count', EQ_ERROR_UNKNOWN);
    }

    return (int) $row['row_count'];
};

$loadMigration = static function(string $file, bool $required = false): array {
    if(!file_exists($file)) {
        if($required) {
            throw new Exception('missing_model_migration_file', EQ_ERROR_INVALID_CONFIG);
        }
        return ['tables' => []];
    }

    $json = file_get_contents($file);
    if($json === false) {
        throw new Exception('unreadable_model_migration_file', EQ_ERROR_INVALID_CONFIG);
    }

    $migration = json_decode($json, true);
    if(!is_array($migration) || json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('invalid_model_migration_file', EQ_ERROR_INVALID_CONFIG);
    }
    if(!isset($migration['tables']) || !is_array($migration['tables'])) {
        throw new Exception('invalid_model_migration_file', EQ_ERROR_INVALID_CONFIG);
    }

    return $migration;
};

$writeMigration = static function(string $file, array $migration): void {
    $directory = dirname($file);
    if(!is_dir($directory) || !is_writable($directory)) {
        throw new Exception('non_writable_model_migration_directory', EQ_ERROR_INVALID_CONFIG);
    }

    $json = json_encode($migration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if($json === false) {
        throw new Exception('model_migration_encoding_failed', EQ_ERROR_UNKNOWN);
    }

    $temporary_file = tempnam($directory, 'model-migration-');
    if($temporary_file === false) {
        throw new Exception('model_migration_temporary_file_failed', EQ_ERROR_UNKNOWN);
    }

    try {
        if(file_put_contents($temporary_file, $json.PHP_EOL) === false) {
            throw new Exception('model_migration_write_failed', EQ_ERROR_UNKNOWN);
        }
        if(!rename($temporary_file, $file)) {
            throw new Exception('model_migration_replace_failed', EQ_ERROR_UNKNOWN);
        }
    }
    finally {
        if(file_exists($temporary_file)) {
            unlink($temporary_file);
        }
    }
};

$discoverModels = static function($orm): array {
    $tables = [];
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

            if(!isset($tables[$table])) {
                $tables[$table] = [
                    'classes'   => [],
                    'models'    => []
                ];
            }

            $tables[$table]['classes'][$type] = true;
            $tables[$table]['models'][$type] = $model;
        }
    }

    foreach($tables as $table => $descriptor) {
        if(count($descriptor['classes']) <= 1) {
            unset($tables[$table]);
            continue;
        }
        ksort($tables[$table]['classes']);
    }
    ksort($tables);

    return $tables;
};

$analyze = static function(array $configured_migration) use($countTableRows, $db, $discoverModels, $normalizeValue, $orm): array {
    $known_tables = array_fill_keys($db->getTables(), true);
    $discovered_tables = $discoverModels($orm);
    $migration = [
        'instructions' => [
            'models'    => 'For each shared table, list the discriminator values assigned to each class. Leave an empty array when no value is assigned to a class.',
            'field'     => 'For a shared table, set field to the column containing the discriminator values.',
            'row_count' => 'Number of rows currently stored in the shared table.'
        ],
        'ready'  => true,
        'tables' => []
    ];

    foreach($discovered_tables as $table => $descriptor) {
        $configured_table = $configured_migration['tables'][$table] ?? [];
        $errors = [];
        $columns = [];
        $observed_values = [];
        $row_count = null;

        if(!is_array($configured_table)) {
            $configured_table = [];
            $errors[] = 'invalid_table_configuration';
        }

        $field = is_string($configured_table['field'] ?? null) ? $configured_table['field'] : '';
        $configured_models = $configured_table['models'] ?? [];
        if(!is_array($configured_models)) {
            $configured_models = [];
            $errors[] = 'invalid_models_configuration';
        }

        $model_values = $configured_models;
        foreach($descriptor['classes'] as $class => $unused) {
            if(!array_key_exists($class, $model_values) || is_null($model_values[$class])) {
                $model_values[$class] = [];
            }
        }
        ksort($model_values);

        if(!isset($known_tables[$table])) {
            $errors[] = 'missing_table';
        }
        else {
            $columns = $db->getTableColumns($table);
            $row_count = $countTableRows($db, $table);
        }

        $mapped_values = [];
        foreach($model_values as $class => $values) {
            if(!isset($descriptor['classes'][$class])) {
                $errors[] = 'unknown_model:'.$class;
                continue;
            }
            if(!is_array($values)) {
                $errors[] = 'invalid_model_values:'.$class;
                continue;
            }
            foreach($values as $value) {
                if(!is_scalar($value) && !is_null($value)) {
                    $errors[] = 'invalid_discriminator_value:'.$class;
                    continue;
                }
                $key = $normalizeValue($value);
                if(isset($mapped_values[$key])) {
                    $errors[] = 'duplicate_discriminator_value:'.json_encode($value);
                    continue;
                }
                $mapped_values[$key] = $class;
            }
        }

        if(!strlen($field)) {
            $errors[] = 'missing_discriminator_field';
        }
        elseif(!in_array($field, $columns, true)) {
            $errors[] = 'unknown_discriminator_field';
        }
        else {
            $counts = [];
            $result = $db->getRecords($table, ['id', $field]);
            while($row = $db->fetchArray($result)) {
                $value = array_key_exists($field, $row) ? $row[$field] : null;
                $key = $normalizeValue($value);
                if(!isset($counts[$key])) {
                    $counts[$key] = ['value' => $value, 'count' => 0];
                }
                ++$counts[$key]['count'];
            }
            ksort($counts);
            $observed_values = array_values($counts);

            foreach($observed_values as $observed) {
                if(!isset($mapped_values[$normalizeValue($observed['value'])])) {
                    $errors[] = 'unmapped_discriminator_value:'.json_encode($observed['value']);
                }
            }
        }

        $errors = array_values(array_unique($errors));
        $migration['tables'][$table] = [
            'classes'         => array_keys($descriptor['classes']),
            'columns'         => array_values($columns),
            'row_count'       => $row_count,
            'field'           => $field,
            'observed_values' => $observed_values,
            'models'          => count($model_values) ? $model_values : (object) [],
            'ready'           => !count($errors),
            'errors'          => $errors
        ];
        if(count($errors)) {
            $migration['ready'] = false;
        }
    }

    return [$migration, $discovered_tables];
};

$migration = $loadMigration($migration_file, $params['phase'] === 'backfill');
[$analysis, $discovered_tables] = $analyze($migration);

if($params['phase'] === 'discover') {
    $writeMigration($migration_file, $analysis);

    $ready_tables = 0;
    foreach($analysis['tables'] as $table) {
        if($table['ready']) {
            ++$ready_tables;
        }
    }

    $context->httpResponse()
        ->status(200)
        ->body([
            'phase'        => 'discover',
            'file'         => 'cache/model-migration.json',
            'tables'       => count($analysis['tables']),
            'ready_tables' => $ready_tables
        ])
        ->send();

    exit(0);
}
elseif($params['phase'] === 'backfill') {
    // Keep the migration file as the source of truth for both configuration and analysis.
    $writeMigration($migration_file, $analysis);

    $not_ready_tables = [];
    foreach($analysis['tables'] as $table => $descriptor) {
        if(!$descriptor['ready']) {
            $not_ready_tables[$table] = $descriptor['errors'];
        }
    }
    if(count($not_ready_tables)) {
        throw new Exception('model_migration_not_ready:'.json_encode($not_ready_tables), EQ_ERROR_INVALID_CONFIG);
    }

    $dap = new DataAdapterProviderSql();
    $summary = [];
    $columns_to_add = [];

    foreach($analysis['tables'] as $table => $descriptor) {
        $columns = $descriptor['columns'];
        if(!in_array('model', $columns, true)) {
            $models = $discovered_tables[$table]['models'];
            $model = reset($models);
            $field = $model->getField('model');
            if(!$field) {
                throw new Exception('missing_model_field', EQ_ERROR_INVALID_CONFIG);
            }

            $adapter = $dap->get($field->getContentType());
            if(!$adapter) {
                throw new Exception('unresolved_adapter', EQ_ERROR_INVALID_CONFIG);
            }
            $type = $adapter->castOutType($field->getUsage());
            if(!strlen($type)) {
                throw new Exception('unresolved_sql_type', EQ_ERROR_INVALID_CONFIG);
            }

            $columns_to_add[$table] = [
                'type' => $type,
                'null' => true
            ];
        }
    }

    foreach($columns_to_add as $table => $definition) {
        $db->sendQuery($db->getQueryAddColumn($table, 'model', $definition));

        $indexed_fields = ['model', 'state', 'deleted', 'id'];
        $existing_fields = $analysis['tables'][$table]['columns'];
        if(empty(array_diff(['state', 'deleted', 'id'], $existing_fields))) {
            $db->sendQuery($db->getQueryAddCompositeIndex($table, $indexed_fields));
        }
    }

    foreach($analysis['tables'] as $table => $descriptor) {
        $updated_rows = 0;
        $is_unambiguous = count($descriptor['classes']) === 1;
        if($is_unambiguous) {
            $class = reset($descriptor['classes']);
            $db->setRecords($table, null, ['model' => $class]);
            $updated_rows += $db->getAffectedRows();
        }
        else {
            foreach($descriptor['models'] as $class => $values) {
                $non_null_values = [];
                $has_null = false;
                foreach($values as $value) {
                    if(is_null($value)) {
                        $has_null = true;
                    }
                    else {
                        $non_null_values[] = $value;
                    }
                }

                if(count($non_null_values)) {
                    $db->setRecords(
                        $table,
                        null,
                        ['model' => $class],
                        [[[$descriptor['field'], 'in', $non_null_values]]]
                    );
                    $updated_rows += $db->getAffectedRows();
                }
                if($has_null) {
                    $db->setRecords(
                        $table,
                        null,
                        ['model' => $class],
                        [[[$descriptor['field'], 'is', null]]]
                    );
                    $updated_rows += $db->getAffectedRows();
                }
            }
        }

        $expected_models = [];
        if(!$is_unambiguous) {
            foreach($descriptor['models'] as $class => $values) {
                foreach($values as $value) {
                    $expected_models[$normalizeValue($value)] = $class;
                }
            }
        }

        $verified_rows = 0;
        $invalid_rows = [];
        $verification_fields = ['id', 'model'];
        if(!$is_unambiguous) {
            $verification_fields[] = $descriptor['field'];
        }
        $result = $db->getRecords($table, $verification_fields);
        while($row = $db->fetchArray($result)) {
            $expected_model = reset($descriptor['classes']);
            if(!$is_unambiguous) {
                $value = array_key_exists($descriptor['field'], $row) ? $row[$descriptor['field']] : null;
                $expected_model = $expected_models[$normalizeValue($value)] ?? null;
            }
            if(is_null($expected_model) || ($row['model'] ?? null) !== $expected_model) {
                if(count($invalid_rows) < 20) {
                    $invalid_rows[] = $row['id'];
                }
                continue;
            }
            ++$verified_rows;
        }

        if(count($invalid_rows)) {
            throw new Exception(
                'model_migration_verification_failed:'.$table.':'.json_encode($invalid_rows),
                EQ_ERROR_UNKNOWN
            );
        }

        $summary[$table] = [
            'updated_rows'  => $updated_rows,
            'verified_rows' => $verified_rows
        ];
    }
}

$context->httpResponse()
    ->status(200)
    ->body([
        'phase'  => 'backfill',
        'tables' => $summary
    ])
    ->send();
