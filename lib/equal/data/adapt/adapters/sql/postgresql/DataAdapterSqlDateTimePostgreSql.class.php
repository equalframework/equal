<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\postgresql;

use equal\data\adapt\adapters\sql\DataAdapterSqlDateTime;

class DataAdapterSqlDateTimePostgreSql extends DataAdapterSqlDateTime {

    public function castOutType($usage=null): string {
        return 'TIMESTAMP WITHOUT TIME ZONE';
    }
}
