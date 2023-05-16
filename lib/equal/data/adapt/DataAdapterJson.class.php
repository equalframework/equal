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

    public function __construct() {
        $this->type = 'json';
    }

    /**
     * Adapts the input value from external type to PHP type (x -> PHP).
     * If necessary (non trivial) Routes the adaptation request to the appropriate method.
     * x -> PHP
     */
	public function adaptIn($value, $usage, $lang='en') {
        // by convention, all types/values are nullable
        if(is_null($value)) {
            return null;
        }
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
                        return hexdec($value);
                    case 'real':
                        return self::jsonToFloat($value);
                }
                break;
            case 'text':
                return (string) $value;
            case 'time':
                return self::jsonToTime($value);
            case 'date':
                switch($subtype) {
                    case 'time':
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
            case 'image':
            case 'binary':
                return self::jsonToBinary($value);
            case 'many2one':
                return self::jsonToMany2One($value);
            case 'one2many':
                return self::jsonToOne2Many($value);
            case 'many2many':
                return self::jsonToMany2Many($value);
            case 'array':
                return self::jsonToArray($value);

        }
        return parent::adaptIn($value, $usage);
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage, $lang='en') {
        if(is_null($value)) {
            return null;
        }
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        // #memo - non listed types means there's nothing to do and value will be returned as is
        switch($type) {
            case 'amount':
                return (float) $value;
            case 'number':
                switch($subtype) {
                    case 'boolean':
                        return (bool) $value;
                    case 'natural':
                    case 'integer':
                        return (int) $value;
                    case 'real':
                        return (float) $value;
                }
                break;
            case 'time':
                return self::timeToJson($value);
            case 'date':
                switch($subtype) {
                    case 'time':
                    case 'plain':
                        return self::datetimeToJson($value);
                    case 'year':
                        // date/year:4 (integer 0-9999)
                        return intval(date('Y', $value));
                    case 'month':
                        // date/month	(integer 1-12, ISO-8601)
                        return date('n', $value);
                        // date/weekday.mon (ISO-8601: 1 to 7, 1 is Monday)
                        // date/weekday.sun (0 to 6, 0 is Sunday)
                        // date/monthday (ISO-8601)
                        // date/yearweek
                        // date/yearday (ISO-8601)
                }
                break;
            case 'image':
            case 'binary':
                return self::binaryToJson($value);
        }
        return parent::adaptOut($value, $usage);
    }


    /* private methods holding the adaptation logic */


    private static function jsonToFloat($value) {
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
                trigger_error("ORM::binary data has not been received or cannot be retrieved", QN_REPORT_WARNING);
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

    private static function jsonToMany2one($value) {
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

    private static function jsonToArray($value) {
        if(!is_array($value)) {
            // try to resolve value as JSON
            $data = @json_decode($value, true, 512, JSON_BIGINT_AS_STRING);
            if(json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $value = $data;
            }
        }
        $to_array = function ($value) use(&$to_array) {
            $result = [];
            $current = "";
            $current_key = '';
            $find_closing_bracket = function ($value, $offset, $open_bracket='[', $close_bracket=']') {
                $count = 0;
                for($i = $offset, $n = strlen($value); $i < $n; ++$i) {
                    $c = substr($value, $i, 1);
                    if($c == $open_bracket) {
                        ++$count;
                    }
                    else if($c == $close_bracket) {
                        --$count;
                        if($count <= 0) {
                            return $i;
                        }
                    }
                }
                return false;
            };

            for($i = 0, $n = strlen($value); $i < $n; ++$i) {
                $c = substr($value, $i, 1);
                // handle array notation
                if($c == '[') {
                    $pos = $find_closing_bracket($value, $i);
                    if($pos === false ) {
                        // malformed array
                        return array($value);
                    }
                    $sub = substr($value, $i+1, $pos-$i-1);
                    $current = '';
                    $res = $to_array($sub);
                    $result[] = $res;
                    $i = $pos;
                    continue;
                }
                // handle sub-object notation
                if($c == '{') {
                    $pos = $find_closing_bracket($value, $i, '{', '}');
                    if($pos === false ) {
                        // malformed array
                        return array($value);
                    }
                    $sub = substr($value, $i+1, $pos-$i-1);
                    $current = '';
                    $res = $to_array($sub);
                    if(strlen($current_key)){
                        $result[trim($current_key)] = $res;
                    }
                    else {
                        $result[] = $res;
                    }
                    $current_key = '';
                    $i = $pos;
                    continue;
                }
                // handle map attribute assignment
                if($c == ':') {
                    $current_key = $current;
                    $current = '';
                    continue;
                }
                // handle separator (next value or next attribute)
                if($c == ',') {
                    if(strlen($current)) {
                        $res = ($current === 'null')?null:json_decode("[\"$current\"]", true, 512, JSON_BIGINT_AS_STRING)[0];
                        if(strlen($current_key)) {
                            $result[trim($current_key)] = $res;
                        }
                        else {
                            $result[] = $res;
                        }
                    }
                    $current_key = '';
                    $current = '';
                    continue;
                }
                // handle double quote notation: keys and values delimited by double-quotes
                if($c == '"') {
                    $pos = strpos($value, '"', $i+1);
                    $sub = substr($value, $i+1, $pos-$i-1);
                    $current .= $sub;
                    $i = $pos;
                    continue;
                }
                $current .= $c;
            }
            if(strlen($current)) {
                $res = ($current === 'null')?null:json_decode("[\"$current\"]", true, 512, JSON_BIGINT_AS_STRING)[0];
                if(strlen($current_key)) {
                    $result[trim($current_key)] = $res;
                }
                else {
                    $result[] = $res;
                }
            }
            return $result;
        };
        if(!is_array($value)) {
            $res = $to_array($value);
            $value = array_pop($res);
        }
        else {
            foreach($value as $key => $val) {
                $res = @json_decode($val, true, 512, JSON_BIGINT_AS_STRING);
                $value[$key] = ($res)?$res:(($val === 'null')?null:$val);
            }
        }
        return (array) $value;
    }

    private static function binaryToJson($value) {
        return base64_encode($value);
    }

    /**
     * Returns date as a ISO 8601 formatted string
     */
    private static function datetimeToJson($value) {
        return date("c", $value);
    }

    private static function timeToJson($value) {
        $hours = floor($value / 3600);
        $minutes = floor(($value % 3600) / 60);
        return sprintf("%02d:%02d", $hours, $minutes);
    }

}