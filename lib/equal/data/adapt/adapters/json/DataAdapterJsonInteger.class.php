<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonInteger implements DataAdapter {

    public function getType() {
        return 'json/integer';
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
        // adapt value from string
        if(is_string($value)) {
            if(in_array($value, ['NULL', 'null'])) {
                $value = null;
            }
            elseif(in_array($value, ['TRUE', 'true', '1'])) {
                $value = 1;
            }
            elseif(in_array($value, ['FALSE', 'false', '0'])) {
                $value = 0;
            }
        }
        // arg represents a numeric value (numeric type or string)
        if(is_numeric($value)) {
            $result = intval($value);
        }
        elseif(is_scalar($value)) {
            $suffixes = [
                'B'  => 1,
                'KB' => 1024,
                'MB' => 1048576,
                'GB' => 1073741824,
                's'  => 1,
                'm'  => 60,
                'h'  => 3600,
                'd'  => 3600*24,
                'w'  => 3600*24*7,
                'M'  => 3600*24*30,
                'Y'  => 3600*24*365
            ];
            $val = (string) $value;
            $intval = intval($val);
            foreach($suffixes as $suffix => $factor) {
                if(strval($intval).$suffix == $val) {
                    $result = $intval * $factor;
                    break;
                }
            }
        }
        return $result;
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
        if(is_null($value)) {
            return null;
        }
        return (int) $value;
    }

}
