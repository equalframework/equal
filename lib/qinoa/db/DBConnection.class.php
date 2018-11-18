<?php
/*
* KNINE php library
*
* DBConnection class
*
*/
namespace qinoa\db;

use qinoa\organic\Service;

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
	}

    public static function constants() {
        return ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS'];
    }
    
	public function connect() {
		if(!isset($this->dbConnection)) return false;
		return $this->dbConnection->connect();
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