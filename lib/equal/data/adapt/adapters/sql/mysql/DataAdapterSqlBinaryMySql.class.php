<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlBinary;

class DataAdapterSqlBinaryMySql extends DataAdapterSqlBinary {

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
