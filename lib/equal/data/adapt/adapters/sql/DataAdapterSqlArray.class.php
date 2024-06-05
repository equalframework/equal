<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\sql;

use equal\data\adapt\DataAdapter;

class DataAdapterSqlArray implements DataAdapter {

    public function getType() {
        return 'sql/array';
    }

    public function castInType(): string {
        return 'array';
    }

    public function castOutType($usage=null): string {
        return 'VARCHAR';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (JSON -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            // try to resolve value as JSON
            $data = @json_decode($value, true, 512, JSON_BIGINT_AS_STRING);
            if(json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $result = $data;
            }
        }
        return (array) $value;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> JSON).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            $result = json_encode($value);
        }
        if(!$result) {
            $result = null;
        }
        return $result;
    }

}
