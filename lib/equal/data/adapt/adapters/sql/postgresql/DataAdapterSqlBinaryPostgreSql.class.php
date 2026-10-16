<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\postgresql;

use equal\data\adapt\adapters\sql\DataAdapterSqlBinary;

class DataAdapterSqlBinaryPostgreSql extends DataAdapterSqlBinary {

    public function castOutType($usage=null): string {
        return 'BYTEA';
    }

    public function adaptIn($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        if(is_string($value) && substr($value, 0, 2) === '\\x') {
            $decoded = hex2bin(substr($value, 2));
            return $decoded === false ? null : $decoded;
        }
        if(function_exists('pg_unescape_bytea')) {
            return pg_unescape_bytea($value);
        }
        return $value;
    }
}
