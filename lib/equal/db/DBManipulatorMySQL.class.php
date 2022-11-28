<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

/**
 * DBManipulator implementation for MySQL server.
 *
 */

class DBManipulatorMySQL extends DBManipulator {


    public static $types_associations = [
        'boolean'       => 'tinyint(4)',        // 1 byte
        'integer'       => 'int(11)',           // 4 bytes
        'float'         => 'decimal(10,2)',     // 5 bytes
        'string'        => 'varchar(255)',      // 255 bytes
        'text'          => 'longtext',          // max 4 GB
        'date'          => 'date',              // 3 bytes
        'time'          => 'time',              // 3 bytes
        'datetime'      => 'datetime',          // 8 bytes
        'binary'        => 'longblob',          // max 4GB
        'many2one'      => 'int(11)'            // 4 bytes
    ];

    public function getSqlType($type) {
        if(isset(self::$types_associations[$type])) {
            return self::$types_associations[$type];
        }
        return '';
    }

    public function select($db_name) {
        return mysqli_select_db($this->dbms_handler, $db_name);
    }

    /**
     * Open the DBMS connection
     *
     * @param   boolean   $auto_select	Automatically connect to provided database (otherwise the connection is established only wity the DBMS server)
     * @return  integer   		        The status of the connect function call.
     * @access  public
     */
    public function connect($auto_select=true) {
        $result = false;
        if($this->canConnect($this->host, $this->port)) {
            if($auto_select) {
                if($this->dbms_handler = mysqli_connect($this->host, $this->user_name, $this->password, $this->db_name, $this->port)) {
                    if($result = $this->select($this->db_name)) {
                        $query = 'set names '.constant('DB_CHARSET');
                        $query .= (defined('DB_COLLATION'))?' collate '.constant('DB_COLLATION'):'';
                        mysqli_query($this->dbms_handler, $query);
                        $result = true;
                    }
                }
            }
            else {
                if($this->dbms_handler = mysqli_connect($this->host, $this->user_name, $this->password, '', $this->port)) {
                    $result = true;
                }
            }
            foreach($this->members as $member) {
                if($member->connect($auto_select) === false) {
                    $result = false;
                    break;
                }
            }
        }
        if(!$result) {
            return false;
        }
        return $this;
    }


    /**
     * Close the DBMS connection
     *
     * @return   integer   Status of the close function call
     * @access   public
     */
    public function disconnect() {
        if(isset($this->dbms_handler)) {
            mysqli_close($this->dbms_handler);
            $this->dbms_handler = null;
            foreach($this->members as $member) {
                $member->disconnect();
            }
        }
        return true;
    }

    public function createDatabase($db_name) {
        $query = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET ".constant('DB_CHARSET')." COLLATE ".constant('DB_COLLATION').';';
        $this->sendQuery($query);
    }

    public function getTableColumns($table_name) {
        $query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table_name' AND TABLE_SCHEMA='".constant('DB_NAME')."';";
        $res = $this->sendQuery($query);

        $columns = [];
        while ($row = $this->fetchArray($res)) {
            $columns[] = $row['COLUMN_NAME'];
        }
        return $columns;
    }

    public function getTableConstraints($table_name) {
        $query = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = '$table_name' AND CONSTRAINT_TYPE = 'UNIQUE';";
        $res = $this->sendQuery($query);
        $constraints = [];
        while ($row = $this->fetchArray($res)) {
            $constraint = $row['CONSTRAINT_NAME'];
            $constraints[] = $constraint;
        }
        return $constraints;
    }

    public function getQueryCreateTable($table_name) {
        // #memo - we must add at least one column, so as a convention we add the id column
        return "CREATE TABLE IF NOT EXISTS `{$table_name}` (`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) DEFAULT CHARSET=".constant('DB_CHARSET')." COLLATE=".constant('DB_COLLATION').";";
    }

    /**
     * Generates one or more SQL queries related to a column creation, according to given column definition.
     *
     * $def structure:
     * [
     *      'type'             => int(11),
     *      'null'             => false,
     *      'default'          => 0,
     *      'auto_incremnt'    => false,
     *      'primary'          => false,
     *      'index'            => false
     * ]
     */
    public function getQueryAddColumn($table_name, $column_name, $def) {
        $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_name}` {$def['type']}";
        if(isset($def['null']) || !$def['null']) {
            $sql .= ' NOT NULL';
        }
        if(isset($def['auto_increment']) && $def['auto_increment']) {
            $sql .= ' AUTO_INCREMENT';
        }
        // #memo - default is supported by ORM, not DBMS
        if(!isset($def['null']) || $def['null']) {
            // unless specified otherwise, all columns can be null (even if having a default value)
            $sql .= ' DEFAULT NULL';
        }

        $sql .= ';';

        if(isset($def['primary']) && $def['primary']) {
            "ALTER TABLE `{$table_name}` ADD PRIMARY KEY (`{$column_name}`);";
        }
        return $sql;
    }

    public function getQueryAddConstraint($table_name, $columns) {
        return "ALTER TABLE `{$table_name}` ADD CONSTRAINT ".implode('_', $columns)." UNIQUE (`".implode('`,`', $columns)."`);";
    }

    public function getQueryAddRecords($table, $fields, $values) {
        $sql = '';
        if (!is_array($fields) || !is_array($values)) {
            throw new \Exception(__METHOD__.' : at least one parameter is missing', QN_ERROR_SQL);
        }
        $cols = '';
        $vals = '';
        foreach ($fields as $field) {
            $cols .= "`$field`,";
        }
        $cols = rtrim($cols, ',');
        foreach ($values as $val_array) {
            $vals .= '(';
            foreach($val_array as $val) {
                $vals .= $this->escapeString($val).',';
            }
            $vals = rtrim($vals, ',').'),';
        }
        $vals = rtrim($vals, ',');
        if(strlen($cols) > 0 && strlen($vals) > 0) {
            // #memo - we ignore duplicate enties, if any
            $sql = "INSERT IGNORE INTO `$table` ($cols) VALUES $vals;";
        }
        return $sql;
    }

    /**
     * Sends a SQL query.
     *
     * @param string The query to send to the DBMS.
     *
     * @return resource Returns a resource identifier or -1 if the query was not executed correctly.
     */
    public function sendQuery($query) {
        trigger_error("QN_DEBUG_SQL::$query", E_USER_NOTICE);

        if(!strlen($query)) {
            trigger_error("QN_DEBUG_SQL::ignoring empty query", QN_REPORT_DEBUG);
            return;
        }

        if(($result = mysqli_query($this->dbms_handler, $query)) === false) {
            throw new \Exception(__METHOD__.' : query failure. '.mysqli_error($this->dbms_handler).'. For query: "'.$query.'"', QN_ERROR_SQL);
        }
        // everything went well: perform additional operations (replication & info about query result)
        else {
            // update $affected_rows, $last_query, $last_id (depending on the performed operation)
            $sql_operation = strtolower((explode(' ', $query, 2))[0]);
            $this->setLastQuery($query);
            if($sql_operation == 'select' || $sql_operation == 'show') {
                $this->setAffectedRows(mysqli_num_rows($result));
            }
            else {
                // for WRITE operations, relay query to members of the replica
                if(in_array($sql_operation, ['insert', 'update', 'delete', 'drop', 'create'])) {
                    foreach($this->members as $member) {
                        $member->sendQuery($query);
                    }
                }
                if($sql_operation =='insert') {
                    $this->setLastId(mysqli_insert_id($this->dbms_handler));
                }
                $this->setAffectedRows(mysqli_affected_rows($this->dbms_handler));
            }
        }
        return $result;
    }

    public static function fetchRow($result) {
        return mysqli_fetch_row($result);
    }

    public static function fetchArray($result) {
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }

    /**
     * Escapes a string containing the name of an object's field to match the SQL notation : `table`.`field` or `field`
     *
     * @param string $field_name
     * @return string
     */
    private static function escapeFieldName($field_name) {
        $parts = explode('.', str_replace('`', '', $field_name));
        return (count($parts) > 1)?"`{$parts[0]}`.`{$parts[1]}`":"`{$parts[0]}`";
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
        else if(in_array(gettype($value), ['integer', 'double'])) {
            $result = $value;
        }
        else if(gettype($value) == 'boolean') {
            $result = ($value)?'1':'0';
        }
        else if(is_null($value)) {
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
                    $result = "UNHEX('".substr($value, 3)."')";
                }
                else {
                    // regular string that must be escaped
                    $result = "'".mysqli_real_escape_string($this->dbms_handler, $value)."'";
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
                if((strcasecmp($cond[1], 'in') == 0 || strcasecmp($cond[1], 'not in') == 0) && !is_array($cond[2])) {
                    $cond[2] = (array) $cond[2];
                }
                // case-sensitive comparison ('like' operator)
                if(strcasecmp($cond[1], 'like') == 0) {
                    // force mysql to convert field to binary (result will be case-sensitive comparison)
                    $cond[0] = 'BINARY '.$cond[0];
                    $cond[1] = 'LIKE';
                }
                // ilike operator does not exist in MySQL
                if(strcasecmp($cond[1], 'ilike') == 0) {
                    // force mysql to handle the field as a char (necessary for translations that are stored in a binary field)
                    $cond[0] = 'CAST('.$cond[0].' AS CHAR )';
                    $cond[1] = 'LIKE';
                }
                // format the value operand
                if(is_array($cond[2])) {
                    $value = '('.implode(',', array_map( [$this, 'escapeString'], $cond[2] )).')';
                }
                else {
                    $value = $this->escapeString($cond[2]);
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

    /**
     * Get records from specified table, according to some conditions.
     *
     * @param	array   $tables       name of involved tables
     * @param	array   $fields       list of requested fields
     * @param	array   $ids          ids to which the selection is limited
     * @param	array   $conditions   list of arrays (field, operand, value)
     * @param	string  $id_field     name of the id field ('id' by default)
     * @param	mixed   $order        string holding name of the order field or maps holding field nmaes as keys and sorting as value
     * @param	integer $start
     * @param	integer $limit
     *
     * @return	resource              reference to query resource
     */
    public function getRecords($tables, $fields=NULL, $ids=NULL, $conditions=NULL, $id_field='id', $order=[], $start=0, $limit=0) {
        // cast tables to an array (passing a single table is accepted)
        if(!is_array($tables)) {
            $tables = (array) $tables;
        }
        // in case fields is not null ans is not an array, cast it to an array (passing a single field is accepted)
        if(isset($fields) && !is_array($fields)) {
            $fields = (array) $fields;
        }
        // in case ids is not null ans is not an array, cast it to an array (passing a single id is accepted)
        if(isset($ids) && !is_array($ids)) {
            $ids = (array) $ids;
        }

        // test values and types
        if(empty($tables)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'tables' array is empty.", QN_ERROR_SQL);
        }
        /* irrelevant
        if(!empty($fields) && !is_array($fields)) throw new \Exception(__METHOD__." : unable to build sql query, parameter 'fields' is not an array.", QN_ERROR_SQL);
        if(!empty($ids) && !is_array($ids)) throw new \Exception(__METHOD__." : unable to build sql query, parameter 'ids' is not an array.", QN_ERROR_SQL);
        */
        if(!empty($conditions) && !is_array($conditions)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'conditions' is not an array.", QN_ERROR_SQL);
        }

        // SELECT clause
        // we could add the following directive for better performance (disabled to maximize code portability)
        // $sql = 'SELECT SQL_CALC_FOUND_ROWS ';
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
                $sql .= '`'.$table_name.'` as `'.$table_alias.'`, ';
            }
            else {
                $sql .= '`'.$table_name.'`, ';
            }
        }
        $sql = rtrim($sql, ' ,');

        // WHERE clause
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);

        // order clause
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
            $sql .= sprintf(" LIMIT %d, %d", $start, $limit);
        }
        return $this->sendQuery($sql);
    }

    public function setRecords($table, $ids, $fields, $conditions=null, $id_field='id'){
        // test values and types
        if(empty($table)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'table' empty.", QN_ERROR_SQL);
        }
        if(empty($fields)) {
            throw new \Exception(__METHOD__." : unable to build sql query, parameter 'fields' empty.", QN_ERROR_SQL);
        }

        // UPDATE clause
        $sql = 'UPDATE `'.$table.'`';

        // SET clause
        $sql .= ' SET ';
        foreach ($fields as $key => $value) {
            $sql .= "`$key`={$this->escapeString($value)}, ";
        }
        $sql = rtrim($sql, ', ');

        // WHERE clause
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);

        return $this->sendQuery($sql);
    }

    /**
     * Inserts new records in specified table.
     *
     * @param	string $table name of the table in which insert the records
     * @param	array $fields list of involved fields
     * @param	array $values array of arrays specifying the values related to each specified field
     * @return	resource reference to query resource
     */
    public function addRecords($table, $fields, $values) {
        if (!is_array($fields) || !is_array($values)) {
            throw new \Exception(__METHOD__.' : at least one parameter is missing', QN_ERROR_SQL);
        }
        $sql = $this->getQueryAddRecords($table, $fields, $values);
        return $this->sendQuery($sql);
    }

    public function deleteRecords($table, $ids, $conditions=null, $id_field='id') {
        // delete clause
        $sql = 'DELETE FROM `'.$table.'`';
        // where clause
        $sql .= $this->getConditionClause($id_field, $ids, $conditions);
        return $this->sendQuery($sql);
    }

}
