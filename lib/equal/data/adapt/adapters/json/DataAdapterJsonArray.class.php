<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonArray implements DataAdapter {

    public function getType() {
        return 'json/array';
    }

    public function castInType(): string {
        return 'array';
    }

    public function castOutType($usage=null): string {
        return 'Array';
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
        // if could not be converted using json_decode, parse the value
        if(!is_array($value)) {
            $res = $to_array($value);
            $value = array_pop($res);
        }
        else {
            // #todo - is this still necessary ?
            /*
            foreach($value as $key => $val) {
                $res = @json_decode($val, true, 512, JSON_BIGINT_AS_STRING);
                $value[$key] = ($res)?$res:(($val === 'null')?null:$val);
            }
            */
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
        if(is_null($value)) {
            return null;
        }
        return (array) $value;
    }

}
