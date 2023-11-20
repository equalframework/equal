<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

class DataAdapterProviderSql implements AdapterProvider {
    const CONFIG = [
        'MYSQL'     => 'equal\data\adapt\DataAdapterProviderSqlMySql',
        'MARIADB'   => 'equal\data\adapt\DataAdapterProviderSqlMySql',
        'SQLSRV'    => 'equal\data\adapt\DataAdapterProviderSqlSqlSrv'
    ];

    /**
     * Provides a DataAdapter instance, according to the given content type.
     * This method supports the Content-Type syntax (type/subtype;parameter=value) along with major format aliases (JSON, SQL, TXT).
     *
     * @param   string      $content_type   The content type or the type-alias for which the DataAdapter has to be returned.
     * We try as much as possible to use standard Content-Types as defined by RFC7231 and listed by IANA (https://www.iana.org/assignments/media-types/media-types.xhtml).
     * When adaptation implies more specific distinction, we use subtype tree for distinguishing adapters.
     *
     * @return DataAdapter
     *
     */
    public function get(string $content_type) {
        static $providers = [];
        $dbms = constant('DB_DBMS');
        if(!isset(self::CONFIG[$dbms])) {
            throw new \Exception('unknown_dbms', QN_ERROR_INVALID_CONFIG);
        }
        if(!isset($providers[$dbms])) {
            $provider_name = self::CONFIG[$dbms];
            $provider = new $provider_name;
            $providers[$dbms] = $provider;
        }
        else {
            $provider = $providers[$dbms];
        }
        return $provider->get($content_type);
    }
}
