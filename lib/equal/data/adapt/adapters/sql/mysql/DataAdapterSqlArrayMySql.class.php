<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlArray;

class DataAdapterSqlArrayMySql extends DataAdapterSqlArray {

    public function getType() {
        return 'sql/array';
    }

    public function castInType(): string {
        return 'array';
    }

    public function castOutType($usage=null): string {
        // max-size of 65k
        return 'TEXT';
    }

}
