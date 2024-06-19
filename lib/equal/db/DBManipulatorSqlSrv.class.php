<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

/**
 * DBManipulator implementation for MS SQL server.
 *
 */
final class DBManipulatorSqlSrv extends DBManipulator {


    /*
        #memo - as from SQL Server 2019, char and varchar types support UTF-8, and collation names are suffixed with '_UTF8'
        For lower versions, we have to use nvarchar.
    */
    public static $types_associations = [
        'boolean'       => 'bit',               // 1 byte
        'integer'       => 'int',               // 4 bytes
        'float'         => 'float(24)',         // 4 bytes
        'string'        => 'nvarchar(255)',     // 510 bytes
        'text'          => 'nvarchar(max)',     // max 2GB
        'date'          => 'date',              // 3 bytes
        'time'          => 'time',              // 5 byte
        'datetime'      => 'datetime2',         // max 8 Bytes
        'binary'        => 'varbinary(max)',    // max 2GB
        'many2one'      => 'int'                // 4 bytes
    ];

    public function getSqlType($type) {
        if(isset(self::$types_associations[$type])) {
            return self::$types_associations[$type];
        }
        return '';
    }

    /**
     * This method has no effect with SQL SRV.
     * There is no distinction between select and connect.
     */
    public function select($db_name) {
        return $this->dbms_handler;
    }

    /**
     * Open the DBMS connection
     *
     * @param   boolean   $auto_select	Automatically connect to provided database (otherwise the connection is established only with the DBMS server)
     * @return  integer   		        The status of the connect function call.
     * @access  public
     */
    public function connect($auto_select=true) {
        $result = false;
        if(!function_exists('sqlsrv_connect')) {
            throw new \Exception('Missing mandatory driver (sqlsrv).', QN_ERROR_UNKNOWN_SERVICE);
        }
        if($this->canConnect($this->host, $this->port)) {
            // prevent warnings from raising errors
            sqlsrv_configure('WarningsReturnAsErrors', 0);
            $connection_info = [
                    'UID'                       => $this->user_name,
                    'PWD'                       => $this->password,
                    // prevent conversion of dates to DateTime objects
                    'ReturnDatesAsStrings'      => true,
                    // allow connection to server with self signed SSL certificate
                    'TrustServerCertificate'    => true,
                    // enable Transparent Network IP Resolution
                    'MultiSubnetFailover'       => 'Yes'
                ];

            if($auto_select) {
                $connection_info['Database'] = $this->db_name;
                // #memo - SQLSRV cluster < 2019 does not support UTF-8
                // #memo - passing an invalid charset raises a IMSSP -1 "invalid option" error
                if(!is_null($this->charset) && strlen($this->charset) > 0) {
                    $connection_info['CharacterSet'] = $this->charset;
                }
            }

            if($this->dbms_handler = sqlsrv_connect($this->host, $connection_info)) {
                $result = true;
            }
            else if( ($errors = sqlsrv_errors()) != null) {
                trigger_error("SQL::".implode(';', array_map(function($a) {return "{$a['SQLSTATE']}, {$a['code']}, {$a['message']}";}, $errors)), QN_REPORT_ERROR);
            }

            foreach($this->members as $member) {
                if($member->connect($auto_select) === false) {
                    $result = false;
                    break;
                }
            }
        }
        else {
            trigger_error("SQL::DBMS host unreachable", QN_REPORT_ERROR);
        }
        if(!$result) {
            return false;
        }
        return $this;
    }

    /**
     * Close the DBMS connection.
     * This method is meant to assign a value to `$this->dbms_handler`.
     *
     * @return   integer   Status of the close function call
     * @access   public
     */
    public function disconnect() {
        if(isset($this->dbms_handler) && $this->dbms_handler !== false) {
            sqlsrv_close($this->dbms_handler);
            $this->dbms_handler = null;
            foreach($this->members as $member) {
                $member->disconnect();
            }
        }
        return true;
    }

    public function createDatabase($db_name) {
        $query = "USE master; CREATE DATABASE $db_name COLLATE ".$this->collation.";";
        $this->sendQuery($query, 'create');
    }

    public function getTables() {
        $tables = [];
        $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE';";
        $res = $this->sendQuery($query);
        while ($row = $this->fetchArray($res)) {
            $tables[] = $row['TABLE_NAME'];
        }
        return $tables;
    }

    public function getTableSchema($table_name) {
        $schema = [];
        // expected properties: COLUMN_NAME, DATA_TYPE, COLLATION_NAME, IS_NULLABLE, COLUMN_DEFAULT
        $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name';";
        $res = $this->sendQuery($query);
        while($row = $this->fetchArray($res)) {
            $field = $row['COLUMN_NAME'];
            $schema[$field] = [
                'type'          => $row['DATA_TYPE'],
                'collation'     => $row['COLLATION_NAME'],
                'nullable'      => $row['IS_NULLABLE'],
                'default'       => $row['COLUMN_DEFAULT']
            ];
        }
        return $schema;
    }

    public function getTableColumns($table_name) {
        $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG = '{$this->db_name}' AND  TABLE_NAME = '$table_name';";
        $res = $this->sendQuery($query);
        $columns = [];
        while ($row = $this->fetchArray($res)) {
            $columns[] = $row['COLUMN_NAME'];
        }
        return $columns;
    }

    public function getTableUniqueConstraints($table_name) {
        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_CATALOG = '{$this->db_name}' AND TABLE_NAME = '$table_name' AND CONSTRAINT_TYPE = 'UNIQUE';";
        $res = $this->sendQuery($query);
        $constraints = [];
        while ($row = $this->fetchArray($res)) {
            $constraint = $row['CONSTRAINT_NAME'];
            if(strpos($constraint, 'AK_') == 0) {
                $constraint = substr($constraint, 3);
            }
            $constraints[] = $constraint;
        }
        return $constraints;
    }

    public function getQueryCreateTable($table_name) {
        // #memo - we must add at least one column for the query to be valid, so as a convention we add the id column
        return "IF (NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_CATALOG = '{$this->db_name}' AND TABLE_NAME = '{$table_name}'))\n
            BEGIN\n
                CREATE TABLE [{$table_name}] ([id] int IDENTITY(1, 1) NOT NULL)\n
            END;";
    }

    /**
     * Generates one or more SQL queries related to a column creation, according to given column definition.
     *
     * $def structure:
     * [
     *      'type'             => int(11),
     *      'null'             => false,
     *      'default'          => 0,
     *      'auto_increment'   => false,
     *      'primary'          => false,
     *      'index'            => false
     * ]
     */
    public function getQueryAddColumn($table_name, $column_name, $def) {
        $sql = "ALTER TABLE [{$table_name}] ADD [{$column_name}] {$def['type']}";
        if(isset($def['null']) && !$def['null']) {
            $sql .= ' NOT NULL';
        }
        // set query according to primary key property
        if(isset($def['primary']) && $def['primary']) {
            $sql .= ' IDENTITY';
            if(isset($def['auto_increment']) && $def['auto_increment']) {
                $sql .= '(1,1)';
            }
        }
        // #memo - default is supported by ORM, not DBMS
        if(!isset($def['null']) || $def['null']) {
            // unless specified otherwise, all columns can be null (even if having a default value)
            $sql .= ' DEFAULT NULL';
        }

        $sql .= ';';

        if(isset($def['primary']) && $def['primary']) {
            $sql .= "ALTER TABLE [{$table_name}] ADD CONSTRAINT PK_{$column_name} PRIMARY KEY({$def['type']});";
        }

        return $sql;
    }

    public function getQueryAddIndex($table_name, $column) {
        return "CREATE INDEX idx_{$column} ON [$table_name] ($column);";
    }

    public function getQueryAddUniqueConstraint($table_name, $columns) {
        return "ALTER TABLE [$table_name] ADD CONSTRAINT ".implode('_', array_merge(['AK'], $columns))." UNIQUE (".implode(',', $columns).");";
    }

    public function getQueryAddRecords($table_name, $fields, $values) {
        $sql = '';
        if (!is_array($fields) || !is_array($values)) {
            throw new \Exception(__METHOD__.' : at least one parameter is missing', QN_ERROR_SQL);
        }
        $vals = [];
        foreach ($values as $val_array) {
            $line = [];
            foreach($val_array as $value) {
                $line[] .= $this->escapeString($value);
            }
            $vals[] = '('.implode(',', $line).')';
        }
        if(count($fields) && count($vals)) {
            // #todo ignore duplicate entries, if any
            $sql = "INSERT INTO [$table_name] (".implode(',', $fields).") OUTPUT INSERTED.id VALUES ".implode(',', $vals).";";
            if(in_array('id', $fields)) {
                $sql = "SET IDENTITY_INSERT $table_name ON;".$sql."SET IDENTITY_INSERT $table_name OFF;";
            }
        }
        return $sql;
    }

    public function getQuerySetRecords($table, $fields) {
        $sql = '';
        // test values and types
        if(empty($table)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'table' empty.", QN_ERROR_SQL);
        }
        if(empty($fields)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'fields' empty.", QN_ERROR_SQL);
        }

        // UPDATE clause
        $sql = "UPDATE [{$table}]";

        // SET clause
        $sql .= ' SET ';
        foreach ($fields as $key => $value) {
            $sql .= "[$key]={$this->escapeString($value)}, ";
        }
        $sql = rtrim($sql, ', ');
        return $sql.';';
    }

    /**
     * Sends a SQL query.
     *
     * @param string The query to send to the DBMS.
     *
     * @return resource Returns a resource identifier or -1 if the query was not executed correctly.
     */
    function sendQuery($query, $sql_operation='') {
        trigger_error("SQL::$query", QN_REPORT_DEBUG);

        if(!strlen($query)) {
            trigger_error("SQL::ignoring empty query", QN_REPORT_DEBUG);
            return;
        }

        if(($result = sqlsrv_query($this->dbms_handler, $query)) === false) {
            if( ($errors = sqlsrv_errors() ) != null) {
                $errors = reset($errors);
                $errors = ((isset($errors['code']))?'('.$errors['code'].') ':'').((isset($errors['message']))?$errors['message']:'');
            }
            throw new \Exception(__METHOD__.' : query failure. '.$errors.'. For query: "'.$query.'"', QN_ERROR_SQL);
        }
        // everything went well: perform additional operations (replication & info about query result)
        else {
            // store query as last query
            $this->setLastQuery($query);
            // update $affected_rows & $last_id (depending on the performed operation)
            if($sql_operation == 'select') {
                $this->setAffectedRows(sqlsrv_num_rows($result));
            }
            elseif(in_array($sql_operation, ['insert', 'update', 'delete', 'drop', 'create'])) {
                // for WRITE operations, relay query to members of the replica
                foreach($this->members as $member) {
                    $member->sendQuery($query);
                }
                if($sql_operation == 'insert') {
                    if($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                        $this->setLastId($row['id']);
                    }
                }
                $this->setAffectedRows(sqlsrv_rows_affected($result));
            }
        }
        return $result;
    }

    public static function fetchRow($result) {
        return sqlsrv_fetch($result);
    }

    public static function fetchArray($result) {
        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    }

    /**
     * Escapes a string containing the name of an object's field to match the SQL notation : `table`.`field` or `field`
     *
     * @param string $field_name
     * @return string
     */
    private static function escapeFieldName($field_name) {
        $parts = explode('.', str_replace('`', '', $field_name));
        return (count($parts) > 1)?"[{$parts[0]}].[{$parts[1]}]":"[{$parts[0]}]";
    }

    /**
     * Escapes a string for safe SQL insertion
     *
     * @param string $value
     * @return string
     */
    private function escapeString($value) {
        $result = '';
        if(gettype($value) == 'string' && strlen($value) == 0) {
            $result = "''";
        }
        elseif(in_array(gettype($value), ['integer', 'double'])) {
            $result = $value;
        }
        elseif(gettype($value) == 'boolean') {
            $result = ($value)?'1':'0';
        }
        elseif(is_null($value)) {
            $result = 'NULL';
        }
        else {
            $value = (string) $value;
            // value is a field name
            if(strlen($value) && substr($value, 0, 1) == '`') {
                $result = self::escapeFieldName($value);
            }
            // value represents NULL SQL value
            else if($value == 'null' || $value == 'NULL') {
                $result = 'NULL';
            }
            // value is any other kind of string
            else {
                if(substr($value, 0, 3) == "h0x") {
                    // hexadecimal string that must be stored as a binary value
                    $result = "".substr($value, 1)."";
                }
                else {
                    // regular string that must be escaped
                    $result = "'".str_replace("'", "''", $value)."'";
                }
            }
        }
        return $result;
    }

    /**
     * Gets the SQL WHERE clause
     *
     * @param string $id_field
     * @param array $ids
     * @param array $conditions
     *
     * array( array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]]) [, array( array(operand, operator, operand)[, array(operand, operator, operand) [, ...]])])
     * array of several series of clauses joined by logical ANDs themselves joined by logical ORs : disjunctions of conjunctions
     * i.e.: (clause[, AND clause [, AND ...]) OR (clause[, AND clause [, AND ...])
     */
    private function getConditionClause($id_field, $ids, $conditions) {
        $sql = '';
        if(empty($conditions)) {
            $conditions = [[[]]];
        }
        for($j = 0, $max_j = count($conditions); $j < $max_j; ++$j) {
            if($j > 0 && strlen($sql) > 0) $sql .= ') OR (';
            if(!empty($ids)) {
                $conditions[$j][] = array($id_field, 'in', $ids);
            }
            for($i = 0, $max_i = count($conditions[$j]); $i < $max_i; ++$i) {
                if($i > 0 && strlen($sql) > 0) {
                    $sql .= ' AND ';
                }
                $cond = $conditions[$j][$i];
                if(!count($cond)) {
                    continue;
                }
                // adjust the field syntax (if necessary)
                $cond[0] = self::escapeFieldName($cond[0]);
                // operator 'in' having a single value as right operand
                if((strcasecmp($cond[1], 'in') == 0 || strcasecmp($cond[1], 'not in') == 0)) {
                    $cond[2] = (array) $cond[2];
                    $value = '('.implode(',', array_map( [$this, 'escapeString'], $cond[2] )).')';
                }
                else {
                    // format the value operand
                    $value = $this->escapeString($cond[2]);
                    // case-sensitive comparison ('like' operator)
                    if(strcasecmp($cond[1], 'like') == 0) {
                        // force mysql to convert field to binary (result will be case-sensitive comparison)
                        $cond[1] = 'LIKE';
                    }
                    // ilike operator does not exist in MySQL
                    elseif(strcasecmp($cond[1], 'ilike') == 0) {
                        // force mysql to handle the field as a char (necessary for translations that are stored in a binary field)
                        $cond[0] = 'UPPER('.$cond[0].')';
                        $cond[1] = 'LIKE';
                        $value = 'UPPER('.$value.')';
                    }
                }
                // concatenate query string with current condition
                $sql .= $cond[0].' '.$cond[1].' '.$value;
            }
        }
        if(strlen($sql) > 0) {
            $sql = ' WHERE ('.$sql.')';
        }
        return $sql;
    }

    public function getRecords($tables, $fields=NULL, $ids=NULL, $conditions=NULL, $id_field='id', $order=[], $start=0, $limit=0) {
        // cast tables to an array (passing a single table is accepted)
        $tables = (array) $tables;

        // check params consistency
        if(empty($tables)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'tables' array is empty.", QN_ERROR_SQL);
        }
        if(!empty($conditions) && !is_array($conditions)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'conditions' is not an array.", QN_ERROR_SQL);
        }

        // normalize columns and ids arrays
        $ids = array_unique((array) $ids);
        $fields = array_unique((array) $fields);

        // SELECT clause
        $sql = 'SELECT DISTINCT ';
        if(empty($fields)) {
            $sql .= '*';
        }
        else {
            $selection = [];
            foreach($fields as $field) {
                $selection[] = self::escapeFieldName($field);
            }
            $sql .= implode(',', $selection);
        }

        // FROM clause
        $sql .= ' FROM ';
        foreach($tables as $table_alias => $table_name) {
            if(!is_numeric($table_alias)) {
                $sql .= '['.$table_name.'] as ['.$table_alias.'], ';
            }
            else {
                $sql .= '['.$table_name.'], ';
            }
        }
        $sql = rtrim($sql, ' ,');

        // WHERE clause
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);

        // ORDER clause
        if(!empty($order)) {
            $order_clause = [];
            if(!is_array($order)) $order = [$order => 'ASC'];
            foreach($order as $field => $sort) {
                $order_clause[] = self::escapeFieldName($field).' '.$sort;
            }
            $sql .= ' ORDER BY '.implode(',', $order_clause);
        }

        // LIMIT clause
        if($limit) {
            $sql .= sprintf(" OFFSET %d ROWS FETCH NEXT %d ROWS ONLY", $start, $limit);
        }
        return $this->sendQuery($sql, 'select');
    }

    public function setRecords($table, $ids, $fields, $conditions=null, $id_field='id'){
        $sql = rtrim($this->getQuerySetRecords($table, $fields), ';');
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);
        return $this->sendQuery($sql, 'update');
    }

    public function addRecords($table, $fields, $values) {
        $sql = $this->getQueryAddRecords($table, $fields, $values);
        return $this->sendQuery($sql, 'insert');
    }

    public function deleteRecords($table, $ids, $conditions=null, $id_field='id') {
        // DELETE statement
        $sql = "DELETE FROM [{$table}]";
        // WHERE clause
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);
        return $this->sendQuery($sql, 'delete');
    }

    /**
     * Fetch and increment the column of a series of records in a single operation.
     *
     * For unknown reason, if the select is done after the update, no result set is returned.
     * That is why we compute the expected result in the first select statement, marked with TABLOCKX to make sure the server locks the table before updating it.
     */
    public function incRecords($table, $ids, $field, $increment, $id_field='id') {
        $sql = 'BEGIN TRANSACTION;';
        $sql .= "SELECT [{$id_field}], [{$field}] FROM [{$table}] WITH (TABLOCKX) WHERE [{$id_field}] in (".implode(',', (array) $ids).");";
        $sql .= "UPDATE [{$table}] SET [{$field}] = [{$field}] + $increment WHERE [{$id_field}] in (".implode(',', $ids).");";
        $sql .= 'COMMIT;';
        return $this->sendQuery($sql, 'update');
    }

}
