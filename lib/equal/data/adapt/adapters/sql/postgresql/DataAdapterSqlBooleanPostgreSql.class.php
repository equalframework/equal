<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\postgresql;

use equal\data\adapt\adapters\sql\DataAdapterSqlBoolean;

class DataAdapterSqlBooleanPostgreSql extends DataAdapterSqlBoolean {

    public function castOutType($usage=null): string {
        return 'BOOLEAN';
    }

    public function adaptIn($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        if(is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string) $value), ['1', 't', 'true', 'y', 'yes'], true);
    }

    public function adaptOut($value, $usage, $locale='en') {
        return is_null($value) ? null : (bool) $value;
    }
}
