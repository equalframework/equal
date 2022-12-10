<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

class DBManipulator implements \equal\data\DataAdapterInterface {

    /**
     * DB server hostname.
     *
     * @var		string
     */
    protected $host;

    /**
     * DB server TCP connection port.
     *
     * @var		integer
     */
    protected $port;

    /**
     * DB name to use for SQL queries.
     *
     * @var		string
     */
    protected $db_name;

    /**
     * Charset encoding to use for communications with DBMS.
     *
     * @var		string
     */
    protected $charset;

    /**
     * Collation to use for storing data (applied on new tables and columns).
     *
     * @var		string
     */
    protected $collation;

    /**
     * DB user
     *
     * @var		string
     */
    protected $user_name;

    /**
     * DB password
     *
     * @var		string
     */
    protected $password;

    /**
     * Latest error id
     *
     * @var		integer
     */
    protected $last_id;

    /**
     * Number of rows affected by last query
     *
     * @var		integer
     */
    protected $affected_rows;

    /**
     * Latest query
     *
     * @var		string
     */
    protected $last_query;

    /**
     * @var     mixed
     */
    protected $dbms_handler;

    /**
     * Array of DBManipulator objects to secondary DBMS / members of the replica, if any
     *
     * @var		array
     * @access	protected
     */
    protected $members;


    /**
     * Class constructor
     *
     * Initialize the DBMS data for SQL transactions
     *
     * @access   public
     * @param    string    DB server hostname
     * @param    string    DB name
     * @param    string    Username to use for the connection
     * @param    string    Password to use for the connection
     * @return   void
     */
    public final function __construct($host, $port, $db, $user, $pass, $charset=null, $collation=null) {
        $this->host         = $host;
        $this->port         = $port;
        $this->db_name      = $db;
        $this->user_name    = $user;
        $this->password     = $pass;
        $this->charset      = $charset;
        $this->collation    = $collation;

        $this->dbms_handler = null;
        $this->members      = [];
    }


    public final function __destruct() {
        $this->disconnect();
    }

    /**
     * Add a replica member
     */
    public function addMember(DBManipulator $member) {
        $this->members[] = $member;
    }

    /**
     * Open the DBMS connection
     *
     * @return   integer   The status of the connect function call (ie: a handler to identify the connection)
     * @access   public
     */
    public function connect($auto_select=true) {
    }

    public function select($db_name) {
    }

    public function connected() {
        return (bool) $this->dbms_handler;
    }

    /**
    * Close the DBMS connection
    *
    * @return   integer   Status of the close function call
    * @access   public
    */
    public function disconnect() {
    }

    /**
     * Returns the SQL type to use for a given ORM type.
     * This method is meant to be overloaded in children DBManipulator classes.
    */
    public function getSqlType($type) {
        return '';
    }

    /**
    * Checks the connectivity to an SQL server
    *
    * @return boolean    false if no connection can be made, true otherwise
    *
    */
    public final function canConnect() {
        if($fp = fsockopen($this->host, $this->port, $errno, $errstr, 1)) {
            fclose($fp);
            return true;
        }
        return false;
    }

    public function createDatabase($db_name) {
    }

    /**
     * Sends a SQL query.
     *
     * @param string The query to send to the DBMS.
     *
     * @return resource Returns a resource identifier or -1 if the query was not executed correctly.
     */
    public function sendQuery($query) {
	}

    public final function isEmpty($value) {
        return (empty($value) || $value == '0000-00-00' || !strcasecmp($value, 'false') || !strcasecmp($value, 'null'));
    }

    public function getLastId() {
        return $this->last_id;
    }


    public function getAffectedRows() {
        return $this->affected_rows;
    }

    public function getLastQuery() {
        return $this->last_query;
    }

    protected function setLastId($id) {
        $this->last_id = $id;
    }

    protected function setAffectedRows($affected_rows) {
        $this->affected_rows = $affected_rows;
    }

    protected function setLastQuery($query) {
        $this->last_query = $query;
    }


    /* Methods from DataAdapterInterface */

    /**
     * This method is meant to be overloaded in children DBManipulator classes.
     */
    public function adaptIn($value, $usage) {

    }

    /**
     * This method is meant to be overloaded in children DBManipulator classes.
     */
    public function adaptOut($value, $usage) {

    }
}