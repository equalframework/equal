<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\sqlite;

use equal\data\adapt\adapters\sql\DataAdapterSqlBinary;

class DataAdapterSqlBinarySqlite extends DataAdapterSqlBinary {

    public function getType() {
        return 'sql/binary';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        return 'BLOB';
    }

}
