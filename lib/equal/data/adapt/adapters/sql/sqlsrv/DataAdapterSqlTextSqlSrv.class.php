<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\sqlite;

use equal\data\adapt\adapters\sql\DataAdapterSqlText;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlTextSqlSrv extends DataAdapterSqlText {

    public function getType() {
        return 'sql/text';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        $type = 'nvarchar(255)';

        if(!is_null($usage)) {
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create($usage);
            }
            $length = $usage->getLength();
            if($length > 255) {
                // up to 1GB
                $type = 'nvarchar(max)';
            }
            else {
                $type = 'nvarchar('.min($length, 1).')';
            }
        }

        return $type;
    }

}
