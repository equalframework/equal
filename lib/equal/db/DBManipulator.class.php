<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;
 
class DBManipulator {

	/**
	 * DB server hostname
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $host;

	/**
	 * DB server connection port
	 *
	 * @var		integer
	 * @access	protected
	 */
	protected $port;

	/**
	 * DB name
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $db_name;


	/**
	 * DB user
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $user_name;


	/**
	 * DB password
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $password;


	/**
	 * Latest error id
	 *
	 * @var		integer
	 * @access	protected
	 */
	protected $last_id;


	/**
	 * Number of rows affected by last query
	 *
	 * @var		integer
	 * @access	protected
	 */
	protected $affected_rows;

	/**
	 * Latest query
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $last_query;

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
	public final function __construct($host, $port, $db, $user, $pass) {
		$this->host         = $host;
		$this->port         = $port;
		$this->db_name      = $db;
		$this->user_name    = $user;
		$this->password     = $pass;
        $this->dbms_handler = null;

		$this->members = [];
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

}