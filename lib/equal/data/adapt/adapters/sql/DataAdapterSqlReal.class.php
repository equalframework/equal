<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql;

use equal\data\adapt\DataAdapter;
use equal\orm\UsageFactory;

class DataAdapterSqlReal implements DataAdapter {

    public function getType() {
        return 'sql/real';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from SQL type to PHP type (SQL -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        // arg represents a numeric value (either numeric type or string)
        if(is_numeric($value)) {
            if(!($usage instanceof Usage)) {
                /** @var \equal\orm\usages\Usage */
                $usage = UsageFactory::create($usage);
            }
            $result = round(floatval($value), $usage->getScale());
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to SQL type (PHP -> SQL).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        if(!($usage instanceof Usage)) {
            /** @var \equal\orm\usages\Usage */
            $usage = UsageFactory::create($usage);
        }
        return number_format($value, $usage->getScale(), '.', '');
    }

}
