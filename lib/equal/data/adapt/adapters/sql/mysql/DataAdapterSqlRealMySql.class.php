<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql\mysql;

use equal\data\adapt\adapters\sql\DataAdapterSqlReal;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterSqlRealMySql extends DataAdapterSqlReal {

    public function getType() {
        return 'sql/real';
    }

    public function castInType(): string {
        return 'float';
    }

    /**
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     */
    public function castOutType($usage=null): string {
        // default values
        $precision = 10;
        $scale = 2;

        // arg represents a numeric value (either numeric type or string)
        if(!is_null($usage)) {
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create($usage);
            }
            $scale = $usage->getScale();
            $precision = $usage->getPrecision() + $scale;
        }

        return 'DECIMAL('.$precision.','.$scale.')';
    }

}
