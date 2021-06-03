<?php
/*
* KNINE php library
*
* DBConnection class
*
*/
namespace equal\db;

use equal\organic\Service;

class DBConnection extends Service {

	private $dbConnection;

	protected function __construct() {
		switch(DB_DBMS) {
			case 'MYSQL' :
				$this->dbConnection = new DBManipulatorMySQL(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD);
				break;
			/*
			// insert handling of other DBMS here
			case 'XYZ' :
				$this->dbConnection = new DBManipulatorXyz(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD);
				break;
			*/
			default:
				$this->dbConnection = null;
		}

		if(defined('DB_REPLICATION') && DB_REPLICATION != 'NO') {
			// add replica members, if any
			$i = 1;

			while(defined('DB_'.$i.'_HOST') && defined('DB_'.$i.'_PORT') && defined('DB_'.$i.'_USER') && defined('DB_'.$i.'_PASSWORD') && defined('DB_'.$i.'_NAME')) {
				$this->addReplicaMember(constant('DB_'.$i.'_HOST'), constant('DB_'.$i.'_PORT'), constant('DB_'.$i.'_NAME'), constant('DB_'.$i.'_USER'), constant('DB_'.$i.'_PASSWORD'));
				++$i;
			}
		}
	}

    public static function constants() {
        return ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'];
    }

	public function addReplicaMember($host, $port, $db, $user, $pass) {
		$member = null;
		switch(DB_DBMS) {
			case 'MYSQL' :
				$member = new DBManipulatorMySQL($host, $port, $db, $user, $pass);
				break;
			/*
			// insert handling of other DBMS here
			case 'XYZ' :
				$this->dbConnection = new DBManipulatorXyz(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD);
				break;
			*/
			default:
				break;
		}
		if($member) {
			$this->dbConnection->addMember($member);
		}
	}

	public function connect($auto_select=true) {
		if(!isset($this->dbConnection)) return false;
		return $this->dbConnection->connect($auto_select);
	}

	public function disconnect() {
		if(!isset($this->dbConnection)) return true;
		return $this->dbConnection->disconnect();
	}


    /**
     * Magic overloading method: catch any call and relay it to dbConnection object
     *
     * @param  string                 $name
     * @param  array                  $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {

        if (!$this->dbConnection) {
            return null;
        }

        return call_user_func_array([$this->dbConnection, $name], $arguments);
    }
    
}