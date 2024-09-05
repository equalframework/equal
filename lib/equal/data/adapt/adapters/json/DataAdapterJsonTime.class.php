<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonTime implements DataAdapter {

    public function getType() {
        return 'json/time';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return 'String';
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
            if(is_numeric($value)) {
                // value is a timestamp, keep it
                $result = intval($value);
            }
            else {
                $count = substr_count($value, ':');
                list($hour, $minute, $second) = [0,0,0];
                if($count == 2) {
                    list($hour, $minute, $second) = sscanf($value, "%d:%d:%d");
                }
                else if($count == 1) {
                    list($hour, $minute) = sscanf($value, "%d:%d");
                }
                else if($count == 0) {
                    $hour = $value;
                }
                $result = ($hour * 3600) + ($minute * 60) + $second;
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
        $hours = floor($value / 3600);
        $minutes = floor(($value % 3600) / 60);
        $seconds = $value % (60);
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

}
