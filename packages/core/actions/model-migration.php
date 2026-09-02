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
    'description'   => 'Discover ORM models using a non-default table and backfill their model discriminator.',
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

            $slug = strtolower(str_replace('\\', '_', $type));
            if(!isset($tables[$table])) {
                $tables[$table] = [
                    'candidate' => false,
                    'classes'   => [],
                    'models'    => []
                ];
            }

            $tables[$table]['candidate'] = $tables[$table]['candidate'] || $slug !== $table;
            $tables[$table]['classes'][$type] = true;
            $tables[$table]['models'][$type] = $model;
        }
    }

    foreach($tables as $table => $descriptor) {
        if(!$descriptor['candidate']) {
            unset($tables[$table]);
            continue;
        }
        ksort($tables[$table]['classes']);
    }
    ksort($tables);

    return $tables;
};

$analyze = static function(array $configured_migration) use($db, $discoverModels, $normalizeValue, $orm): array {
    $known_tables = array_fill_keys($db->getTables(), true);
    $discovered_tables = $discoverModels($orm);
    $migration = ['tables' => []];

    foreach($discovered_tables as $table => $descriptor) {
        $configured_table = $configured_migration['tables'][$table] ?? [];
        $field = is_string($configured_table['field'] ?? null) ? $configured_table['field'] : '';
        $model_values = is_array($configured_table['models'] ?? null) ? $configured_table['models'] : [];
        $errors = [];
        $columns = [];
        $observed_values = [];

        if(!isset($known_tables[$table])) {
            $errors[] = 'missing_table';
        }
        else {
            $columns = $db->getTableColumns($table);
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
            'field'           => $field,
            'observed_values' => $observed_values,
            'models'          => count($model_values) ? $model_values : (object) [],
            'ready'           => !count($errors),
            'errors'          => $errors
        ];
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

            $db->sendQuery($db->getQueryAddColumn($table, 'model', [
                'type' => $type,
                'null' => true
            ]));
        }

        $updated_rows = 0;
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

        $expected_models = [];
        foreach($descriptor['models'] as $class => $values) {
            foreach($values as $value) {
                $expected_models[$normalizeValue($value)] = $class;
            }
        }

        $verified_rows = 0;
        $invalid_rows = [];
        $result = $db->getRecords($table, ['id', $descriptor['field'], 'model']);
        while($row = $db->fetchArray($result)) {
            $value = array_key_exists($descriptor['field'], $row) ? $row[$descriptor['field']] : null;
            $expected_model = $expected_models[$normalizeValue($value)] ?? null;
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
