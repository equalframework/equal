<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\db;


/**
 * This class uses factory pattern for providing DBManipulator instances.
 */
class DBConnection {

    public static function create(string $dbms='', string $host='', int $port=null, string $name='', string $user='', string $password='', string $charset='', string $collation='') {
        /** @var DBManipulator */
        $dbConnection = null;

        switch($dbms) {
            case 'MARIADB':
            case 'MYSQL' :
                $dbConnection = new DBManipulatorMySQL($host, $port, $name, $user, $password, $charset, $collation);
                break;
            case 'SQLSRV' :
                $dbConnection = new DBManipulatorSqlSrv($host, $port, $name, $user, $password, $charset, $collation);
                break;
            case 'SQLITE' :
                $dbConnection = new DBManipulatorSQLite($host, $port, $name, $user, $password, $charset, $collation);
                break;
            case 'POSTGRESQL' :
                // #todo
                break;
            case 'ORACLE' :
                // #todo
                break;
        }
        return $dbConnection;
    }

}
