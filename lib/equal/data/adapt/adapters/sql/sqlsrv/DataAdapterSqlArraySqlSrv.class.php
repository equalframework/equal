<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\sqlite;

use equal\data\adapt\adapters\sql\DataAdapterSqlArray;

class DataAdapterSqlArraySqlSrv extends DataAdapterSqlArray {

    public function getType() {
        return 'sql/array';
    }

    public function castInType(): string {
        return 'array';
    }

    public function castOutType($usage=null): string {
        return 'VARCHAR';
    }

}
