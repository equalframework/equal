<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlText;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlTextMySql extends DataAdapterSqlText {

    public function getType() {
        return 'sql/text';
    }

    public function castInType(): string {
        return 'string';
    }

    public function castOutType($usage=null): string {
        $type = 'VARCHAR(255)';

        if(!is_null($usage)) {
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create($usage);
            }
            $length = $usage->getLength();
            if($length > 16777215) {
                // up to 4GB
                $type = 'LONGTEXT';
            }
            elseif($length > 65535) {
                // up to 16MB
                $type = 'MEDIUMTEXT';
            }
            elseif($length > 255) {
                // up to 65KB
                $type = 'TEXT';
            }
            else {
                $type = 'VARCHAR('.max($length, 1).')';
            }
        }

        return $type;
    }

}
