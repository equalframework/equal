<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;

use equal\organic\Service;

/**
 * Service for connecting to the DBMS holding the database of the current installation.
 * This service acts as a facade for DB interactions.
 */
class DBConnector extends Service {

    /** @var DBManipulator */
    private $connection;

    public static function constants() {
        return ['DB_DBMS', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_CHARSET', 'DB_COLLATION'];
    }

    protected function __construct() {
        $this->connection = DBConnection::create(
                constant('DB_DBMS'),
                constant('DB_HOST'),
                constant('DB_PORT'),
                constant('DB_NAME'),
                constant('DB_USER'),
                constant('DB_PASSWORD'),
                constant('DB_CHARSET'),
                constant('DB_COLLATION')
            );

        if(defined('DB_REPLICATION') && constant('DB_REPLICATION') != 'NO') {
            // add replica members, if any
            $i = 1;

            while(defined('DB_'.$i.'_HOST')
                && defined('DB_'.$i.'_PORT')
                && defined('DB_'.$i.'_USER')
                && defined('DB_'.$i.'_PASSWORD')
                && defined('DB_'.$i.'_NAME')) {

                $this->addReplicaMember(
                    constant('DB_'.$i.'_HOST'),
                    constant('DB_'.$i.'_PORT'),
                    constant('DB_'.$i.'_NAME'),
                    constant('DB_'.$i.'_USER'),
                    constant('DB_'.$i.'_PASSWORD')
                );
                ++$i;
            }
        }
    }

    public function addReplicaMember($host, $port, $db, $user, $pass) {
        /** @var DBManipulator */
        $member = DBConnection::create(constant('DB_DBMS'), $host, $port, $db, $user, $pass);
        if($member && $this->connection) {
            $this->connection->addMember($member);
        }
    }

    public function connect($auto_select=true) {
        return isset($this->connection) && $this->connection->connect($auto_select);
    }

    public function disconnect() {
        return !isset($this->connection) || $this->connection->disconnect();
    }

    /**
     * Magic overloading method: catch any call and relay it to DBManipulator object
     *
     * @param  string                 $name
     * @param  array                  $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {
        if (!$this->connection) {
            return null;
        }
        return call_user_func_array([$this->connection, $name], $arguments);
    }

}
