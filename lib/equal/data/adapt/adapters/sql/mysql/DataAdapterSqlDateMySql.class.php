<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlDate;

class DataAdapterSqlDateMySql extends DataAdapterSqlDate {

    public function getType() {
        return 'sql/date';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return 'DATE';
    }

}
