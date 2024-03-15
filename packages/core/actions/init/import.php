<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\data\adapt\DataAdapter;
use equal\db\DBConnection;
use equal\db\DBManipulator;
use equal\db\DBManipulatorMySQL;
use equal\db\DBManipulatorSQLite;
use equal\db\DBManipulatorSqlSrv;

$getImportConfig = function($config_file_path): array {
    $config_file_content = file_get_contents($config_file_path);
    if(!$config_file_content) {
        throw new Exception('Missing import config file ' . $config_file_path, QN_ERROR_INVALID_CONFIG);
    }

    $import_config = json_decode($config_file_content, true);
    if(!is_array($import_config)) {
        throw new Exception('Invalid import configuration file', QN_ERROR_INVALID_CONFIG);
    }

    return $import_config;
};

$createOldDbConnection = function(string $dbms, string $host, int $port, string $name, string $user, string $password, string $charset, string $collation) {
    $db_manipulator_map = [
        'MARIADB' => DBManipulatorMySQL::class,
        'MYSQL'   => DBManipulatorMySQL::class,
        'SQLSRV'  => DBManipulatorSqlSrv::class,
        'SQLITE'  => DBManipulatorSQLite::class,
    ];

    if(!isset($db_manipulator_map[$dbms])) {
        throw new Exception('DBMS ' . $dbms . ' not handled', QN_ERROR_INVALID_CONFIG);
    }

    $db_connection = new $db_manipulator_map[$dbms]($host, $port, $name, $user, $password, $charset, $collation);

    $db_connection->connect();
    if(!$db_connection->connected()) {
        throw new Exception(
            'Unable to establish connection to DBMS host (wrong credentials) for old connection',
            QN_ERROR_INVALID_CONFIG
        );
    }

    return $db_connection;
};

$createNewDbConnection = function() {
    $db_connection = DBConnection::getInstance(
        constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'),
        constant('DB_USER'), constant('DB_PASSWORD'),
        constant('DB_DBMS')
    );

    $db_connection->connect();
    if(!$db_connection->connected()) {
        throw new Exception('Unable to establish connection to DBMS host (wrong credentials)', QN_ERROR_INVALID_CONFIG);
    }

    return $db_connection;
};

$castOldDbRow = function(array $old_item, array $old_table_fields_config): array {
    $old_item_casted = [];
    foreach($old_item as $key => $column_value) {
        switch($old_table_fields_config[$key]) {
            case 'integer':
                $old_item_casted[$key] = (int) $column_value;
                break;
            case 'float':
                $old_item_casted[$key] = (float) $column_value;
                break;
            case 'boolean':
                $old_item_casted[$key] = (bool) $column_value;
                break;
            case 'date':
                $old_item_casted[$key] = strtotime($column_value);
                break;
            default:
                $old_item_casted[$key] = $column_value;
                break;
        }
    }

    return $old_item_casted;
};

$createNewItemFromOld = function(array $config, DBManipulator $old_db_connection, array $old_item): array {
    $item = [
        'creator'  => QN_ROOT_USER_ID,
        'created'  => time(),
        'modifier' => QN_ROOT_USER_ID,
        'modified' => time(),
        'deleted'  => false,
        'state'    => 'instance'
    ];

    foreach($config['data_map'] as $new_key => $import_conf) {
        if(is_string($import_conf)) {
            $import_conf = [
                'type'  => 'field',
                'field' => $import_conf
            ];
        }

        $imp_confs = isset($import_conf['type']) ? [$import_conf] : $import_conf;

        $previous_value = $old_item[$imp_confs[0]['field']] ?? null;
        foreach($imp_confs as $imp_conf) {
            switch($imp_conf['type']) {
                case 'value':
                    $item[$new_key] = $import_conf['value'];
                    break;
                case 'field':
                    $item[$new_key] = $previous_value;
                    break;
                case 'computed':
                    $item[$new_key] = $imp_conf['value'];
                    foreach($imp_conf['fields'] as $f) {
                        $item[$new_key] = str_replace('%'.$f.'%', $old_item[$f], $item[$new_key]);
                    }
                    break;
                case 'cast':
                    switch($imp_conf['cast']) {
                        case 'integer':
                            $item[$new_key] = (int) $previous_value;
                            break;
                        case 'boolean':
                            $item[$new_key] = (bool) $previous_value;
                            break;
                        case 'string':
                            $item[$new_key] = (string) $previous_value;
                            break;
                    }
                    break;
                case 'round':
                    $item[$new_key] = round($previous_value);
                    break;
                case 'multiply':
                    $item[$new_key] = $previous_value * $import_conf['by'];
                    break;
                case 'divide':
                    $item[$new_key] = $previous_value / $import_conf['by'];
                    break;
                case 'field-contains':
                    $item[$new_key] = strpos($previous_value, $imp_conf['value']) !== false;
                    break;
                case 'field-does-not-contain':
                    $item[$new_key] = strpos($previous_value, $imp_conf['value']) === false;
                    break;
                case 'map-value':
                    $match_found = false;
                    foreach($imp_conf['map'] as $map_item) {
                        if($map_item['old'] != $previous_value) {
                            continue;
                        }

                        $item[$new_key] = $map_item['new'];
                        $match_found = true;
                        break;
                    }

                    if(!$match_found) {
                        $item[$new_key] = $previous_value;
                    }

                    break;
                case 'query':
                    if(is_null($previous_value)) {
                        break;
                    }

                    $query = $imp_conf['query'];
                    $resRel = $old_db_connection->sendQuery(
                        'SELECT `' . $query['field'] . '` from `' . $query['table'] . '` WHERE `' . ($query['where_field'] ?? 'id') . '` = ' . $previous_value . ' LIMIT 1;'
                    );

                    if($relRow = $old_db_connection->fetchArray($resRel)) {
                        $item[$new_key] = $relRow[$query['field']];
                    }
                    else {
                        $item[$new_key] = null;
                    }

                    break;
            }

            $previous_value =  $item[$new_key];
        }
    }

    return $item;
};

list($params, $providers) = eQual::announce([
    'description' => 'Import data from a database to eQual database for a given package.',
    'help'        => 'Needs a configuration file init/import-config.json in the folder of the concerned package.',
    'params'      => [
        'db_dbms' => [
            'type'    => 'string',
            'default' => 'MYSQL'
        ],

        'db_host' => [
            'type'     => 'string',
            'required' => true
        ],

        'db_port' => [
            'type'     => 'integer',
            'required' => true
        ],

        'db_user' => [
            'type'     => 'string',
            'required' => true
        ],

        'db_password' => [
            'type'     => 'string',
            'required' => true
        ],

        'db_name' => [
            'type'     => 'string',
            'required' => true
        ],

        'db_charset' => [
            'type'     => 'string',
            'default'  => 'utf8mb4'
        ],

        'db_collation' => [
            'type'     => 'string',
            'default'  => 'utf8mb4_unicode_ci'
        ],

        'package' => [
            'type'     => 'string',
            'required' => true
        ],

        'entity' => [
            'type'     => 'string'
        ]
    ],
    'response'    => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'   => ['context', 'adapt']
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $dap) = [$providers['context'], $providers['adapt']];

/** @var $adapter DataAdapter */
$adapter = $dap->get('sql');

$import_config = $getImportConfig(
    QN_BASEDIR . '/packages/' . $params['package'] . '/init/import-config.json'
);

$old_db_connection = $createOldDbConnection(
    $params['db_dbms'], $params['db_host'], $params['db_port'], $params['db_name'],
    $params['db_user'], $params['db_password'], $params['db_charset'], $params['db_collation']
);

$new_db_connection = $createNewDbConnection();

$limit = 500;

foreach($import_config as $config) {
    if(isset($params['entity']) && $params['entity'] !== $config['entity']) {
        continue;
    }

    $new_table_name = str_replace('\\', '_', $config['entity']);

    $offset = 0;
    $remaining_data = true;
    while($remaining_data) {
        $res = $old_db_connection->getRecords(
            $config['old_table']['name'],
            array_keys($config['old_table']['fields']),
            null,
            $config['old_table']['conditions'] ?? null,
            $config['old_table']['id_field'] ?? 'id',
            [],
            $offset,
            $limit
        );

        if($res->num_rows < $limit) {
            $remaining_data = false;
        }

        $items = [];
        while ($row = $old_db_connection->fetchArray($res)) {
            $old_item = $castOldDbRow($row, $config['old_table']['fields']);
            $items[] = $createNewItemFromOld($config, $old_db_connection, $old_item);
        }

        if(!empty($items)) {
            foreach($items as &$item) {
                $item['created'] = $adapter->adaptOut($item['created'], 'datetime');
                $item['modified'] = $adapter->adaptOut($item['modified'], 'datetime');
            }

            $new_db_connection->addRecords(
                $new_table_name,
                array_keys($items[0]),
                $items
            );
        }

        $offset += $limit;
    }
}

$old_db_connection->disconnect();
$new_db_connection->disconnect();

$context->httpResponse()
        ->body(['success' => true])
        ->send();
