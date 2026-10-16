<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

/**
 * DBManipulator implementation for PostgreSQL server.
 */
final class DBManipulatorPostgreSql extends DBManipulator {

    public static $types_associations = [
        'boolean'       => 'boolean',
        'integer'       => 'integer',
        'float'         => 'numeric(10,2)',
        'string'        => 'varchar(255)',
        'text'          => 'text',
        'date'          => 'date',
        'time'          => 'time',
        'datetime'      => 'timestamp without time zone',
        'binary'        => 'bytea',
        'many2one'      => 'integer'
    ];

    public function getSqlType($type) {
        if(isset(self::$types_associations[$type])) {
            return self::$types_associations[$type];
        }
        return '';
    }

    /**
     * PostgreSQL cannot switch database on an open connection, so selecting a
     * database requires reconnecting to it.
     */
    public function select($db_name) {
        if(isset($this->dbms_handler) && $this->dbms_handler !== false) {
            pg_close($this->dbms_handler);
        }

        $this->db_name = $db_name;
        $this->dbms_handler = $this->openConnection($db_name);
        if($this->dbms_handler === false) {
            return false;
        }

        return $this->configureConnection();
    }

    public function connect($auto_select=true) {
        if(!function_exists('pg_connect')) {
            throw new \Exception('missing_dependency', QN_ERROR_INVALID_CONFIG);
        }

        if(!$this->canConnect()) {
            trigger_error('SQL::DBMS host unreachable', QN_REPORT_ERROR);
            return false;
        }

        if($auto_select) {
            $this->dbms_handler = $this->openConnection($this->db_name);
        }
        else {
            // Every PostgreSQL connection targets a database. Use the standard
            // maintenance database and fall back to template1 when unavailable.
            $this->dbms_handler = $this->openConnection('postgres');
            if($this->dbms_handler === false) {
                $this->dbms_handler = $this->openConnection('template1');
            }
        }

        if($this->dbms_handler === false || !$this->configureConnection()) {
            $this->dbms_handler = null;
            return false;
        }

        foreach($this->members as $member) {
            if($member->connect($auto_select) === false) {
                return false;
            }
        }

        return $this;
    }

    public function disconnect() {
        if(isset($this->dbms_handler) && $this->dbms_handler !== false) {
            pg_close($this->dbms_handler);
            $this->dbms_handler = null;
            foreach($this->members as $member) {
                $member->disconnect();
            }
        }
        return true;
    }

    public function createDatabase($db_name) {
        $db_name_literal = pg_escape_literal($this->dbms_handler, $db_name);
        $result = $this->sendQuery("SELECT 1 FROM pg_database WHERE datname = {$db_name_literal};", 'select');
        if($this->fetchRow($result)) {
            return;
        }

        $query = 'CREATE DATABASE '.$this->escapeIdentifier($db_name);
        if($this->charset) {
            $query .= ' ENCODING '.pg_escape_literal($this->dbms_handler, $this->normalizeCharset($this->charset));
            $query .= ' TEMPLATE template0';
        }
        $this->sendQuery($query.';', 'create');
    }

    public function getTables() {
        $tables = [];
        $query = "SELECT table_name
                    FROM information_schema.tables
                   WHERE table_schema = current_schema()
                     AND table_type = 'BASE TABLE'
                ORDER BY table_name;";
        $res = $this->sendQuery($query, 'select');
        while($row = $this->fetchArray($res)) {
            $tables[] = $row['table_name'];
        }
        return $tables;
    }

    public function getTableSchema($table_name) {
        $schema = [];
        $table_literal = pg_escape_literal($this->dbms_handler, $table_name);
        $query = "SELECT column_name, data_type, collation_name, is_nullable, column_default
                    FROM information_schema.columns
                   WHERE table_schema = current_schema()
                     AND table_name = {$table_literal}
                ORDER BY ordinal_position;";
        $res = $this->sendQuery($query, 'select');
        while($row = $this->fetchArray($res)) {
            $schema[$row['column_name']] = [
                'type'      => $this->normalizeSchemaType($row['data_type']),
                'collation' => $row['collation_name'],
                'nullable'  => $row['is_nullable'],
                'default'   => $row['column_default']
            ];
        }
        return $schema;
    }

    public function getTableColumns($table_name) {
        $columns = [];
        $table_literal = pg_escape_literal($this->dbms_handler, $table_name);
        $query = "SELECT column_name
                    FROM information_schema.columns
                   WHERE table_schema = current_schema()
                     AND table_name = {$table_literal}
                ORDER BY ordinal_position;";
        $res = $this->sendQuery($query, 'select');
        while($row = $this->fetchArray($res)) {
            $columns[] = $row['column_name'];
        }
        return $columns;
    }

    public function getTableUniqueConstraints($table_name) {
        $constraints = [];
        $table_literal = pg_escape_literal($this->dbms_handler, $table_name);
        $query = "SELECT constraint_name
                    FROM information_schema.table_constraints
                   WHERE table_schema = current_schema()
                     AND table_name = {$table_literal}
                     AND constraint_type = 'UNIQUE';";
        $res = $this->sendQuery($query, 'select');
        while($row = $this->fetchArray($res)) {
            $constraints[] = $row['constraint_name'];
        }
        return $constraints;
    }

    public function getCompositeIndexName($columns) {
        $index_name = 'idx_'.implode('_', $columns ?? []);
        if(strlen($index_name) > 63) {
            $index_name = 'idx_'.md5($index_name);
        }
        return $index_name;
    }

    public function getQueryCreateTable($table_name) {
        $table = $this->escapeIdentifier($table_name);
        return "CREATE TABLE IF NOT EXISTS {$table} (\"id\" INTEGER GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY);";
    }

    public function getQueryAddColumn($table_name, $column_name, $def) {
        $table = $this->escapeIdentifier($table_name);
        $column = $this->escapeIdentifier($column_name);
        $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$def['type']}";

        if(isset($def['auto_increment']) && $def['auto_increment']) {
            $sql .= ' GENERATED BY DEFAULT AS IDENTITY';
        }
        if(isset($def['primary']) && $def['primary']) {
            $sql .= ' PRIMARY KEY';
        }
        if(isset($def['null']) && !$def['null']) {
            $sql .= ' NOT NULL';
        }
        else {
            $sql .= ' DEFAULT NULL';
        }

        return $sql.';';
    }

    public function getQueryAddIndex($table_name, $column) {
        $index_name = $this->getIndexName($table_name, [$column]);
        return 'CREATE INDEX IF NOT EXISTS '.$this->escapeIdentifier($index_name)
            .' ON '.$this->escapeIdentifier($table_name)
            .' ('.$this->escapeIdentifier($column).');';
    }

    public function getQueryAddCompositeIndex($table_name, $columns) {
        $index_name = $this->getIndexName($table_name, $columns);
        $escaped_columns = array_map(fn($column) => $this->escapeIdentifier($column), $columns);
        return 'CREATE INDEX IF NOT EXISTS '.$this->escapeIdentifier($index_name)
            .' ON '.$this->escapeIdentifier($table_name)
            .' ('.implode(',', $escaped_columns).');';
    }

    public function getQueryAddUniqueConstraint($table_name, $columns) {
        $constraint_name = $this->getIndexName($table_name, $columns, 'uk');
        $escaped_columns = array_map(fn($column) => $this->escapeIdentifier($column), $columns);
        return 'ALTER TABLE '.$this->escapeIdentifier($table_name)
            .' ADD CONSTRAINT '.$this->escapeIdentifier($constraint_name)
            .' UNIQUE ('.implode(',', $escaped_columns).');';
    }

    public function getQueryAddRecords($table, $fields, $values) {
        if(!is_array($fields) || !is_array($values)) {
            throw new \Exception(__METHOD__.' : at least one parameter is missing', QN_ERROR_SQL);
        }

        $columns = array_map(fn($field) => $this->escapeIdentifier($field), $fields);
        $rows = [];
        foreach($values as $value_row) {
            $rows[] = '('.implode(',', array_map([$this, 'escapeString'], $value_row)).')';
        }

        if(!count($columns) || !count($rows)) {
            return '';
        }

        return 'INSERT INTO '.$this->escapeIdentifier($table)
            .' ('.implode(',', $columns).') VALUES '.implode(',', $rows)
            .' ON CONFLICT DO NOTHING RETURNING "id";';
    }

    public function getQuerySetRecords($table, $fields) {
        if(empty($table)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'table' empty.", QN_ERROR_SQL);
        }
        if(empty($fields)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'fields' empty.", QN_ERROR_SQL);
        }

        $assignments = [];
        foreach($fields as $field => $value) {
            $assignments[] = $this->escapeIdentifier($field).'='.$this->escapeString($value);
        }

        return 'UPDATE '.$this->escapeIdentifier($table).' SET '.implode(', ', $assignments).';';
    }

    public function sendQuery($query, $sql_operation='') {
        trigger_error("SQL::$query", QN_REPORT_DEBUG);

        if(!strlen($query)) {
            trigger_error('SQL::ignoring empty query', QN_REPORT_DEBUG);
            return;
        }

        $result = @pg_query($this->dbms_handler, $query);
        if($result === false) {
            $error_msg = pg_last_error($this->dbms_handler);
            trigger_error("SQL::error while executing query: {$error_msg}", QN_REPORT_ERROR);
            throw new \Exception(__METHOD__.' : query failure ('.$error_msg.') for query: "'.$query.'"', QN_ERROR_SQL);
        }

        $this->setLastQuery($query);
        if(!strlen($sql_operation)) {
            $status = strtolower(pg_result_status($result, PGSQL_STATUS_STRING));
            $sql_operation = strtok($status, ' ');
        }
        $sql_operation = strtolower($sql_operation);

        if($sql_operation === 'select' || $sql_operation === 'show') {
            $this->setAffectedRows(pg_num_rows($result));
        }
        else {
            if(in_array($sql_operation, ['insert', 'update', 'delete', 'drop', 'create', 'alter', 'truncate'])) {
                foreach($this->members as $member) {
                    if($member instanceof self) {
                        $member->sendQuery($query, $sql_operation);
                    }
                    else {
                        $member->sendQuery($query);
                    }
                }
            }

            if($sql_operation === 'insert' && pg_num_rows($result) > 0) {
                for($i = 0, $count = pg_num_fields($result); $i < $count; ++$i) {
                    if(pg_field_name($result, $i) === 'id') {
                        $this->setLastId(pg_fetch_result($result, 0, $i));
                        break;
                    }
                }
            }
            $this->setAffectedRows(pg_affected_rows($result));
        }

        return $result;
    }

    public static function fetchRow($result) {
        return pg_fetch_row($result);
    }

    public static function fetchArray($result) {
        return pg_fetch_assoc($result);
    }

    private function openConnection($db_name) {
        $parts = [
            'host='.$this->escapeConnectionParameter($this->host),
            'port='.$this->escapeConnectionParameter($this->port),
            'dbname='.$this->escapeConnectionParameter($db_name),
            'user='.$this->escapeConnectionParameter($this->user_name),
            'password='.$this->escapeConnectionParameter($this->password),
            'connect_timeout=2'
        ];
        return @pg_connect(implode(' ', $parts), PGSQL_CONNECT_FORCE_NEW);
    }

    private function configureConnection() {
        if($this->charset && pg_set_client_encoding($this->dbms_handler, $this->normalizeCharset($this->charset)) !== 0) {
            trigger_error('SQL::unable to set PostgreSQL client encoding', QN_REPORT_ERROR);
            return false;
        }
        if(@pg_query($this->dbms_handler, 'SET search_path TO public;') === false) {
            return false;
        }
        return true;
    }

    private function normalizeCharset($charset) {
        if(in_array(strtolower($charset), ['utf8', 'utf-8', 'utf8mb4'])) {
            return 'UTF8';
        }
        return $charset;
    }

    private function normalizeSchemaType($type) {
        $types = [
            'boolean'                     => 'bool',
            'smallint'                    => 'smallint',
            'integer'                     => 'int',
            'bigint'                      => 'bigint',
            'numeric'                     => 'decimal',
            'real'                        => 'real',
            'double precision'            => 'float',
            'character'                   => 'char',
            'character varying'           => 'varchar',
            'text'                        => 'text',
            'date'                        => 'date',
            'time without time zone'      => 'time',
            'time with time zone'         => 'time',
            'timestamp without time zone' => 'datetime',
            'timestamp with time zone'    => 'datetime',
            'bytea'                       => 'blob'
        ];
        return $types[$type] ?? $type;
    }

    private function escapeConnectionParameter($value) {
        return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value)."'";
    }

    private function escapeIdentifier($identifier) {
        return '"'.str_replace('"', '""', (string) $identifier).'"';
    }

    private function escapeFieldName($field_name) {
        $parts = explode('.', str_replace('`', '', $field_name));
        return implode('.', array_map(fn($part) => $this->escapeIdentifier($part), $parts));
    }

    private function escapeString($value) {
        if(gettype($value) === 'string' && strlen($value) === 0) {
            return "''";
        }
        if(in_array(gettype($value), ['integer', 'double'])) {
            return (string) $value;
        }
        if(gettype($value) === 'boolean') {
            return $value ? 'TRUE' : 'FALSE';
        }
        if(is_null($value)) {
            return 'NULL';
        }

        $value = (string) $value;
        if(strlen($value) && substr($value, 0, 1) === '`') {
            return $this->escapeFieldName($value);
        }
        if($value === 'null' || $value === 'NULL') {
            return 'NULL';
        }
        if(substr($value, 0, 3) === 'h0x') {
            return "decode('".substr($value, 3)."', 'hex')";
        }
        return pg_escape_literal($this->dbms_handler, $value);
    }

    private function getConditionClause($id_field, $ids, $conditions) {
        $sql = '';
        if(empty($conditions)) {
            $conditions = [[[]]];
        }

        for($j = 0, $max_j = count($conditions); $j < $max_j; ++$j) {
            if($j > 0 && strlen($sql) > 0) {
                $sql .= ') OR (';
            }
            if(!empty($ids)) {
                $conditions[$j][] = [$id_field, 'in', $ids];
            }
            for($i = 0, $max_i = count($conditions[$j]); $i < $max_i; ++$i) {
                if($i > 0 && strlen($sql) > 0) {
                    $sql .= ' AND ';
                }
                $condition = $conditions[$j][$i];
                if(!count($condition)) {
                    continue;
                }

                $field = $this->escapeFieldName($condition[0]);
                $operator = $condition[1];
                $value = $condition[2];

                if((strcasecmp($operator, 'in') === 0 || strcasecmp($operator, 'not in') === 0) && !is_array($value)) {
                    $value = (array) $value;
                }
                if(strcasecmp($operator, 'like') === 0) {
                    $operator = 'LIKE';
                }
                elseif(strcasecmp($operator, 'ilike') === 0) {
                    $operator = 'ILIKE';
                }

                if(is_array($value)) {
                    $value = '('.implode(',', array_map([$this, 'escapeString'], $value)).')';
                }
                else {
                    $value = $this->escapeString($value);
                }
                $sql .= $field.' '.$operator.' '.$value;
            }
        }

        return strlen($sql) ? ' WHERE ('.$sql.')' : '';
    }

    private function getIndexName($table_name, $columns, $prefix='idx') {
        $name = $prefix.'_'.$table_name.'_'.implode('_', $columns);
        if(strlen($name) > 63) {
            $name = $prefix.'_'.md5($name);
        }
        return $name;
    }

    private function synchronizeIdentitySequence($table) {
        $table_literal = pg_escape_literal($this->dbms_handler, $table);
        $table_identifier = $this->escapeIdentifier($table);
        $query = "SELECT setval(
                        pg_get_serial_sequence({$table_literal}, 'id'),
                        GREATEST(
                            COALESCE(MAX(\"id\"), 1),
                            nextval(pg_get_serial_sequence({$table_literal}, 'id'))
                        ),
                        true
                    )
                    FROM {$table_identifier};";
        $result = @pg_query($this->dbms_handler, $query);
        if($result === false) {
            throw new \Exception(__METHOD__.' : unable to synchronize identity sequence', QN_ERROR_SQL);
        }
    }

    public function getRecords($tables, $fields=null, $ids=null, $conditions=null, $id_field='id', $order=[], $start=0, $limit=0) {
        $tables = (array) $tables;
        if(isset($fields) && !is_array($fields)) {
            $fields = (array) $fields;
        }
        if(isset($ids) && !is_array($ids)) {
            $ids = (array) $ids;
        }

        if(empty($tables)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'tables' is empty.", QN_ERROR_SQL);
        }
        if(!empty($conditions) && !is_array($conditions)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'conditions' is not an array.", QN_ERROR_SQL);
        }

        $sql = 'SELECT DISTINCT ';
        if(empty($fields)) {
            $sql .= '*';
        }
        else {
            $sql .= implode(',', array_map([$this, 'escapeFieldName'], $fields));
        }

        $from = [];
        foreach($tables as $alias => $table_name) {
            $table = $this->escapeIdentifier($table_name);
            if(!is_numeric($alias)) {
                $table .= ' AS '.$this->escapeIdentifier($alias);
            }
            $from[] = $table;
        }
        $sql .= ' FROM '.implode(', ', $from);
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);

        if(!empty($order)) {
            if(!is_array($order)) {
                $order = [$order => 'ASC'];
            }
            $order_clause = [];
            foreach($order as $field => $sort) {
                $order_clause[] = $this->escapeFieldName($field).' '.$sort;
            }
            $sql .= ' ORDER BY '.implode(',', $order_clause);
        }

        if($limit) {
            $sql .= sprintf(' LIMIT %d OFFSET %d', $limit, $start);
        }
        return $this->sendQuery($sql, 'select');
    }

    public function setRecords($table, $ids, $fields, $conditions=null, $id_field='id') {
        $sql = rtrim($this->getQuerySetRecords($table, $fields), ';');
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);
        return $this->sendQuery($sql, 'update');
    }

    public function addRecords($table, $fields, $values) {
        $sql = $this->getQueryAddRecords($table, $fields, $values);
        $result = $this->sendQuery($sql, 'insert');

        if(in_array('id', $fields, true)) {
            $this->synchronizeIdentitySequence($table);
            foreach($this->members as $member) {
                if($member instanceof self) {
                    $member->synchronizeIdentitySequence($table);
                }
            }
        }

        return $result;
    }

    public function deleteRecords($table, $ids, $conditions=null, $id_field='id') {
        $sql = 'DELETE FROM '.$this->escapeIdentifier($table);
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);
        return $this->sendQuery($sql, 'delete');
    }

    public function incRecords($table, $ids, $field, $increment, $id_field='id') {
        $table_name = $this->escapeIdentifier($table);
        $id_column = $this->escapeIdentifier($id_field);
        $field_column = $this->escapeIdentifier($field);
        $id_values = implode(',', array_map([$this, 'escapeString'], (array) $ids));
        $increment_value = $this->escapeString($increment);

        $sql = "WITH old_values AS (
                    SELECT {$id_column}, {$field_column}
                      FROM {$table_name}
                     WHERE {$id_column} IN ({$id_values})
                       FOR UPDATE
                ), updated AS (
                    UPDATE {$table_name} AS target
                       SET {$field_column} = target.{$field_column} + {$increment_value}
                      FROM old_values
                     WHERE target.{$id_column} = old_values.{$id_column}
                 RETURNING target.{$id_column}
                )
                SELECT old_values.{$id_column}, old_values.{$field_column}
                  FROM old_values
                  JOIN updated ON updated.{$id_column} = old_values.{$id_column};";

        return $this->sendQuery($sql, 'update');
    }
}
