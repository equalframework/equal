<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonDate implements DataAdapter {

    public function getType() {
        return 'json/date';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return 'Date';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (JSON -> PHP).
     * The value is handled as a UTC timestamp or ISO 8601 string.
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(is_numeric($value)) {
            // value is a timestamp, keep it
            $result = intval($value);
        }
        else {
            // convert ISO 8601 to timestamp
            $result = strtotime($value);
        }
        if(!$result) {
            $result = null;
        }
        else {
            $result = strtotime(date('Y-m-d', $result).'T00:00:00Z');
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> JSON).
     * Converts a timestamp to an ISO 8601 string (date @ 00:00:00).
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
        return date('Y-m-d', $value).'T00:00:00+00:00';
    }

}
