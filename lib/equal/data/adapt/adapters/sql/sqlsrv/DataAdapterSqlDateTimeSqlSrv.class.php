<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\sqlite;

use equal\data\adapt\adapters\sql\DataAdapterSqlDateTime;

class DataAdapterSqlDateTimeSqlSrv extends DataAdapterSqlDateTime {

    public function getType() {
        return 'sql/datetime';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return 'datetime2';
    }

}
