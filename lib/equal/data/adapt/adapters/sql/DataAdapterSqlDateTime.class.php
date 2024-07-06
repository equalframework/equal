<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql;

use equal\data\adapt\DataAdapter;

class DataAdapterSqlDateTime implements DataAdapter {

    public function getType() {
        return 'sql/datetime';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return 'DATETIME';
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
        if(!is_null($value)) {
            // return SQL date as a timestamp
            list($year, $month, $day, $hour, $minute, $second) = sscanf($value, "%d-%d-%d %d:%d:%d");
            $result = mktime($hour, $minute, $second, $month, $day, $year);
        }
        return $result ?? null;
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
        return date('Y-m-d H:i:s', $value);
    }

}
