<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data;

/**
 *
 * This class is intended to be used as a helper for back-end direct values conversions.
 * It holds a single static method `convert()` for converting a value, according to a given usage.
 *
 */


class DataConverter {

    /**
     * Converts a string to an integer based on its usage.
     *
     * @param mixed  $value The value to convert (can be a string, integer, or other type).
     * @param string $usage (optional) Specifies the conversion context (e.g., 'numeric/integer', 'time/duration', ...).
     *
     * @return mixed Returns the converted integer value, the original value (if no usage match), or null if applicable or explicitly set to null.
     */
    public static function convert($value, $usage='', $format='') {
        switch($usage) {
            case 'number':
            case 'number/natural':
            case 'number/integer':
                return self::convert_number($value);
            case 'time':
            case 'time/duration':
                return self::convert_time($value);
        }
        return $value;
    }

    /**
     * Converts a date to a string to an integer with support for ISO-80000 IEC-60027 suffixes.
     */
    private static function convert_number($value) {
        $result = null;
        if(is_null($value)) {
            return null;
        }
        // adapt value from string
        if(is_string($value)) {
            if(in_array($value, ['NULL', 'null'])) {
                return null;
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
            $val = (string) $value;
            $suffixes = [
                'b'   => 1,
                'B'   => 1,
                'k'   => 1000,
                'K'   => 1000,
                'kb'  => 1000,
                'KB'  => 1000,
                'kib' => 1024,
                'KiB' => 1024,
                'm'   => 1000000,
                'M'   => 1000000,
                'mb'  => 1000000,
                'MB'  => 1000000,
                'mib' => 1048576,
                'MiB' => 1048576,
                'g'   => 1000000000,
                'gb'  => 1000000000,
                'gib' => 1073741824,
                'GiB' => 1073741824
            ];
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
     * Converts a date to a string to an integer with support for ISO 8601 suffixes.
     */
    private static function convert_time($value) {
        $result = null;
        if(is_null($value)) {
            return null;
        }
        if(is_numeric($value)) {
            // value is a timestamp (in seconds)
            $result = intval($value);
        }
        elseif(is_scalar($value)) {
            $val = (string) $value;
            if(strpos($val, ':') !== false) {
                $count = substr_count($val, ':');
                list($hour, $minute, $second) = [0,0,0];
                if($count == 2) {
                    list($hour, $minute, $second) = sscanf($val, "%d:%d:%d");
                }
                elseif($count == 1) {
                    list($hour, $minute) = sscanf($val, "%d:%d");
                }
                $result = ($hour * 3600) + ($minute * 60) + $second;
            }
            else {
                $suffixes = [
                    'ms' => 0.001,
                    's'  => 1,
                    'm'  => 60,
                    'h'  => 3600,
                    'd'  => 3600*24,
                    'D'  => 3600*24,
                    'w'  => 3600*24*7,
                    'M'  => 3600*24*30,
                    'y'  => 3600*24*365,
                    'Y'  => 3600*24*365
                ];
                $intval = intval($val);
                foreach($suffixes as $suffix => $factor) {
                    if(strval($intval).$suffix == $val) {
                        $result = $intval * $factor;
                        break;
                    }
                }
            }
        }
        return $result;
    }


}