<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;


class DataAdapter implements DataAdapterInterface {

    public function __construct() {
    }

    public static function strToInteger($value) {
        if(is_string($value)) {
            if($value == 'null') {
                $value = null;
            }
            elseif(empty($value)) {
                $value = 0;
            }
            elseif(in_array($value, ['TRUE', 'true'])) {
                $value = 1;
            }
            elseif(in_array($value, ['FALSE', 'false'])) {
                $value = 0;
            }
        }
        // arg represents a numeric value (numeric type or string)
        if(is_numeric($value)) {
            $value = intval($value);
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
                    $value = $intval * $factor;
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * Default method for data adaptation
     * This is used as fallback when usage did not trigger any other adaptation, and simply return the original value unchanged.
     */
    protected function adaptDefault($value) {
        return $value;
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * x -> PHP
     *
     */
	public function adaptIn($value, $usage, $lang='en') {
        return $this->adaptDefault($value);
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage, $lang='en') {
        return $this->adaptDefault($value);
    }

}