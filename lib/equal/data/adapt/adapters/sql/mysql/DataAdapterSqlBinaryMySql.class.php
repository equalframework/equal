<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlBinary;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlBinaryMySql extends DataAdapterSqlBinary {

    public function getType() {
        return 'sql/binary';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        $type = 'BLOB';
        if(!is_null($usage)) {
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create($usage);
            }
            $length = $usage->getLength();
            if($length > 16777215) {
                // up to 4GB
                $type = 'LONGBLOB';
            }
            elseif($length > 65535) {
                // up to 16MB
                $type = 'MEDIUMBLOB';
            }
        }
        return $type;
    }

}
