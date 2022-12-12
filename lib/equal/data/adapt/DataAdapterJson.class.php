<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterJson extends DataAdapter {

   /**
     * Adapts the input value from external type to PHP type (x -> PHP).
     * If necessary (non trivial) Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     *
     */
	public function adaptIn($value, $usage) {
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        switch($type) {
            case 'number':
                switch($subtype) {
                    case 'boolean':
                        return self::jsonToBoolean($value);
                    case 'natural':
                    case 'integer':
                        return self::strToInteger($value);
                    case 'hexadecimal':
                        return is_null($value)?null:hexdec($value);
                    case 'real':
                        return self::jsonToFloat($value);
                }
                break;
            case 'text':
                break;
            case 'time':
                return self::jsonToTime($value);
            case 'date':
                switch($subtype) {
                    case 'plain':
                        return self::jsonToDatetime($value);
                    case 'year':
                        // date/year:4 (integer 0-9999)
                    case 'month':
                        // date/month	(integer 1-12, ISO-8601)
                        // date/weekday.mon (ISO-8601: 1 to 7, 1 is Monday)
                        // date/weekday.sun (0 to 6, 0 is Sunday)
                        // date/monthday (ISO-8601)
                        // date/yearweek
                        // date/yearday (ISO-8601)
                }
                break;
            case 'datetime':
                return self::jsonToDatetime($value);
            case 'image':
            case 'binary':
                return self::jsonToBinary($value);
            case 'many2one':
                return self::jsonToMany2One($value);
            case 'one2many':
                return self::jsonToOne2Many($value);
            case 'many2many':
                return self::jsonToMany2Many($value);
        }
        return parent::adaptIn($value, $usage);
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage) {
        return parent::adaptOut($value, $usage);
    }



    /* private methods holding the adaptation logic */


    private static function jsonToFloat($value) {
        if(is_string($value) && $value == 'null') {
            $value = null;
        }
        // arg represents a numeric value (either numeric type or string)
        if(is_numeric($value)) {
            $value = floatval($value);
        }
        return $value;
    }

    private static function jsonToBoolean($value) {
        if(in_array($value, ['TRUE', 'true', '1', 1, true], true)) {
            $value = true;
        }
        elseif(in_array($value, ['FALSE', 'false', '0', 0, false], true)) {
            $value = false;
        }
        return $value;
    }


    private static function jsonToBinary($value) {
        /*
            $value is expected to be either a base64 string,
            or an array holding data from the $_FILES array (multipart/form-data) having the following keys set:

            [
                'name'      string  filename provided by client
                'type'      string  MIME type / content-type
                'size'      int     size in bytes
                'tmp_name'  string  current path of temp file holding binary data
                'error'     int     error code
            ]
        */
        if(!is_array($value)) {
            // handle data URL notation
            if(substr($value, 0, 5) == 'data:') {
                // extract the base64 part @see RFC2397 (data:[<mediatype>][;base64],<data>)
                $value = str_replace(' ', '+', substr($value, strpos($value, ',')+1) );
            }
            $res = base64_decode($value, true);
            if(!$res) {
                throw new \Exception(serialize(["not_valid_base64" => "Invalid base64 data URI value."]), QN_ERROR_INVALID_PARAM);
            }
            if(strlen($res) > constant('UPLOAD_MAX_FILE_SIZE')) {
                throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
            }
        }
        else {
            // #todo - this should be done with PHP context extraction
            if(!isset($value['tmp_name'])) {
                // throw new \Exception("binary data has not been received or cannot be retrieved", QN_ERROR_UNKNOWN);
                trigger_error("QN_DEBUG_ORM::binary data has not been received or cannot be retrieved", QN_REPORT_WARNING);
                $res = '';
            }
            else {
                if(isset($value['error']) && $value['error'] == 2 || isset($value['size']) && $value['size'] > constant('UPLOAD_MAX_FILE_SIZE')) {
                    throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                }
                $res = file_get_contents($value['tmp_name']);
            }
        }
        return $res;
    }

    private static function jsonToDatetime($value) {
        if(is_numeric($value)) {
            // value is a timestamp, keep it
            $value = intval($value);
        }
        else {
            // convert ISO 8601 to timestamp
            $value = strtotime($value);
            if($value === false) {
                return null;
            }
        }
        return $value;
    }

    private static function jsonToMany2one($value) {
        // consider empty string as null
        if(is_string($value) && (!strlen($value) || $value == 'null')) {
            $value = null;
        }
        if(!is_null($value) && !is_numeric($value)) {
            if(is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }
            else {
                $out_value = serialize($value);
                throw new \Exception(serialize(["not_valid_identifier" => "Format inconvertible to id (integer) for $out_value."]), QN_ERROR_INVALID_PARAM);
            }
        }
        $value = (int) $value;
        // setting to zero unsets the field
        if($value == 0) {
            $value = null;
        }
        return $value;
    }

    private static function jsonToTime($value) {
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
        return ($hour * 3600) + ($minute * 60) + $second;
    }

    private static function jsonToOne2Many($value) {
        if(is_string($value)) {
            $value = array_filter(explode(',', $value), function ($a) { return is_numeric($a); });
        }
        return $value;
    }

    private static function jsonToMany2Many($value) {
        // we got a string: convert to array
        if(is_string($value)) {
            $value = trim($value, "[]");
            $value = explode(',', $value);
        }
        else if(!is_array($value)) {
            $value = (array) $value;
        }
        // check for recursion
        if(isset($value[0]) && is_array($value[0])) {
            $value = array_map(function($a) { return isset($a['id'])?$a['id']:'?'; }, $value);
        }
        // make sure array contains only numbers or numeric strings
        $value = array_filter($value, function ($a) { return is_numeric($a); });
        return $value;
    }

}