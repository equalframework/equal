<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\postgresql;

use equal\data\adapt\adapters\sql\DataAdapterSqlText;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlTextPostgreSql extends DataAdapterSqlText {

    public function castOutType($usage=null): string {
        if(is_null($usage)) {
            return 'VARCHAR(255)';
        }
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }

        $length = $usage->getLength();
        if($length > 0 && $length <= 10485760) {
            return 'VARCHAR('.$length.')';
        }
        return 'TEXT';
    }
}
