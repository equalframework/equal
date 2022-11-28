<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

use equal\organic\Service;

class DBConnection extends Service {

    /**
     * @var DBManipulator
     */
    private $dbConnection;

    protected function __construct() {
        switch(constant('DB_DBMS')) {
            case 'MARIADB':
            case 'MYSQL' :
                $this->dbConnection = new DBManipulatorMySQL(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'));
                break;
            case 'SQLSRV' :
                $this->dbConnection = new DBManipulatorSqlSrv(constant('DB_HOST'), constant('DB_PORT'), constant('DB_NAME'), constant('DB_USER'), constant('DB_PASSWORD'));
                break;
            case 'POSTGRE' :
                // #todo
                break;
            case 'ORACLE' :
                // #todo
                break;
            default:
                $this->dbConnection = null;
        }

        if(defined('DB_REPLICATION') && constant('DB_REPLICATION') != 'NO') {
            // add replica members, if any
            $i = 1;

            while(defined('DB_'.$i.'_HOST') && defined('DB_'.$i.'_PORT') && defined('DB_'.$i.'_USER') && defined('DB_'.$i.'_PASSWORD') && defined('DB_'.$i.'_NAME')) {
                $this->addReplicaMember(constant('DB_'.$i.'_HOST'), constant('DB_'.$i.'_PORT'), constant('DB_'.$i.'_NAME'), constant('DB_'.$i.'_USER'), constant('DB_'.$i.'_PASSWORD'));
                ++$i;
            }
        }
    }

    public static function constants() {
        return ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_DBMS', 'DB_CHARSET', 'DB_COLLATION'];
    }

    public function addReplicaMember($host, $port, $db, $user, $pass) {
        $member = null;
        switch(constant('DB_DBMS')) {
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
        if($member && $this->dbConnection) {
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