<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data;

use equal\organic\Service;
use equal\fs\FSManipulator;


class IntegerAdapter {
    public function adapt($to, $from) {

    }
}
class DataAdapter extends Service {

    private $config;

    protected function __construct(/* no dependency */) {
        // initial configuration
        $this->config = null;
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

    private function init() {
        $this->config = [
            'boolean' => [
                'txt' => [
                    'php' =>    function ($value) {
                                    if(in_array($value, ['TRUE', 'true', '1', 1, true], true)) return true;
                                    return false;
                                }
                ],
                'sql' => [
                    'php' =>    function ($value) {
                                    return (bool) (intval($value) > 0);
                                }
                ],
                'php' => [
                    'txt' =>    function ($value) {
                                    //return ($value)?'true':'false';
                                    return (bool) $value;
                                },
                    'sql' =>    function ($value) {
                                    return ($value)?'1':'0';
                                }
                ]
            ],
            'integer' => [
                'txt' => [
                    'php' =>    function ($value) {
                                    $value = self::strToInteger($value);
                                    if(!is_int($value)) {
                                        $out_value = serialize($value);
                                        throw new \Exception(serialize(["not_valid_integer" => "Format inconvertible to integer for {$out_value}."]), QN_ERROR_INVALID_PARAM);
                                    }
                                    return $value;
                                }
                ],
                'sql' => [
                    'php' =>    function ($value) {
                                    // todo : handle numbers of arbitrary length (> 10 digits)
                                    return intval($value);
                                }
                ]
            ],
            'float' => [
                'txt'   => [
                    'php' =>    function ($value) {
                                    if(is_string($value)) {
                                        if($value == 'null') $value = null;
                                    }
                                    // arg represents a numeric value (either numeric type or string)
                                    if(is_numeric($value)) {
                                        $value = floatval($value);
                                    }
                                    else if(!is_null($value)) {
                                        throw new \Exception(serialize(["not_valid_float" => "Format inconvertible to float."]), QN_ERROR_INVALID_PARAM);
                                    }
                                    return $value;
                                }
                ],
                'sql' => [
                    'php' =>    function ($value) {
                                    return floatval($value);
                                }
                ]
            ],
            'string' => [
                'txt'   => [
                    'php' =>    function ($value) {
                                    // #memo - urldecoding (and any pre-treatment) should not occur here
                                    return $value;
                                }
                ],
                'php' => [
                    'sql' =>    function($value) {
                                    // make sure the string does not have a binary notation
                                    if(substr($value, 0, 3) == 'h0x') {
                                        // trim binary prefix
                                        $value = substr($value, 1);
                                    }
                                    return $value;
                                }
                ]
            ],
            'time' => [
                // string times are expected to be ISO 8601 formatted times (hh:mm:ss)
                'txt'   => [
                    'php' =>    function ($value) {
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

                ],
                // internally, times are handled as relative timestamps (number of seconds since the beginning of the day)
                'php'   => [
                    'txt' =>    function($value)  {
                                    $hours = (int) ($value / (60*60));
                                    $minutes = (int) (($value % (60*60)) / 60);
                                    $seconds = $value % (60);
                                    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                },
                    'sql' =>    function($value)  {
                                    $hours = (int) ($value / (60*60));
                                    $minutes = (int) (($value % (60*60)) / 60);
                                    $seconds = $value % (60);
                                    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                }
                ],
                'sql' => [
                    'php' =>    function($value) {
                                    list($hour, $minute, $second) = sscanf($value, "%d:%d:%d");
                                    return ($hour * 3600) + ($minute * 60) + $second;
                                }
                ]
            ],
            'date' => [
                // string dates are expected to be UTC timestamps or ISO 8601 formatted dates
                'txt'   => [
                    'php' =>    function ($value) {
                                    if(is_numeric($value)) {
                                        // value is a timestamp, keep it
                                        $value = intval($value);
                                    }
                                    else {
                                        // convert ISO8601 to timestamp
                                        $value = strtotime($value);
                                        if($value === false) {
                                            // throw new \Exception(serialize(["not_valid_date" => "Format inconvertible to date."]), QN_ERROR_INVALID_PARAM);
                                            return null;
                                        }
                                    }
                                    return $value;
                                }
                ],
                // internally, we handle dates as timestamps
                'php'   => [
                    'txt' =>    function ($value) {
                                    if(is_null($value)) {
                                        return null;
                                    }
                                    // return date as a ISO 8601 formatted string
                                    return date("c", $value);
                                },
                    'sql' =>    function ($value) {
                                    if(is_null($value)) {
                                        return 'NULL';
                                    }
                                    return date('Y-m-d', $value);
                                }
                ],
                'sql'   => [
                    'php' =>    function ($value) {
                                    if(is_null($value)) {
                                        return null;
                                    }
                                    // return date as a timestamp
                                    list($year, $month, $day) = sscanf($value, "%d-%d-%d");
                                    return mktime(0, 0, 0, $month, $day, $year);
                                }
                ]
            ],
            'datetime' => [
                // string dates are expected to be UTC timestamps or ISO 8601 formatted dates
                'txt'   => [
                    'php' =>    function ($value) {
                                    if(is_numeric($value)) {
                                        // value is a timestamp, keep it
                                        $value = intval($value);
                                    }
                                    else {
                                        // convert ISO 8601 to timestamp
                                        $value = strtotime($value);
                                        if($value === false) {
                                            // throw new \Exception(serialize(["not_valid_datetime" => "Format inconvertible to datetime."]), QN_ERROR_INVALID_PARAM);
                                            return null;
                                        }
                                    }
                                    return $value;
                                }
                ],
                // internally, we handle dates as timestamps
                'php'   => [
                    'txt' =>    function ($value) {
                                    if(is_null($value)) {
                                        return null;
                                    }
                                    // return date as a ISO 8601 formatted string
                                    return date("c", $value);
                                },
                    'sql' =>    function ($value) {
                                    if(is_null($value)) {
                                        return 'NULL';
                                    }
                                    return date('Y-m-d H:i:s', $value);
                                }
                ],
                'sql'   => [
                    'php' =>    function ($value) {
                                    if(is_null($value)) {
                                        return null;
                                    }
                                    // return SQL date as a timestamp
                                    list($year, $month, $day, $hour, $minute, $second) = sscanf($value, "%d-%d-%d %d:%d:%d");
                                    return mktime($hour, $minute, $second, $month, $day, $year);
                                }
                ]
            ],

            // 'array' is a type only used in announce() method
            'array' => [
                'txt'   => [
                    'php' =>    function ($value) {
                                    // try to resolve value as JSON
                                    $data = @json_decode($value, true);
                                    if(json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                                        $value = $data;
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
                                                    if(strlen($current_key)) {
                                                        $result[trim($current_key)] = json_decode("[\"$current\"]")[0];
                                                    }
                                                    else {
                                                        $result[] = json_decode("[\"$current\"]")[0];
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
                                            if(strlen($current_key)) {
                                                $result[trim($current_key)] = json_decode("[\"$current\"]")[0];
                                            }
                                            else {
                                                $result[] = json_decode("[\"$current\"]")[0];
                                            }
                                        }
                                        return $result;
                                    };
                                    if(!is_array($value)) {
                                        $res = $to_array($value);
                                        $value = array_pop($res);
                                    }
                                    return (array) $value;
                                }
                ],
                'php'   => [
                    'txt' =>    function ($value) {
                                    return (array) $value;
                                }
                ]
            ],
            'many2one'  => [
                'txt' => [
                    'php' =>    function ($value) {
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
                ],
                'sql' => [
                    'php' =>    function ($value) {
                                    return (int) $value;
                                }
                ]
            ],
            'one2many'  => [
                'txt' => [
                    'php' =>    function ($value) {
                                    if(is_string($value)) {
                                        $value = array_filter(explode(',', $value), function ($a) { return is_numeric($a); });
                                    }
                                    return $value;
                                }
                ],
                'php'   => [
                    'txt' =>    function ($value) {
                                    return (array) $value;
                                }
                ]
            ],
            'many2many' => [
                'txt' => [
                    'php' =>    function($value) {
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
                ],
                'php'   => [
                    'txt' =>    function ($value) {
                                    return (array) $value;
                                }
                ]
            ],
            'html' => [
                'txt' => [
                    'php' => function ($value) {
                        // clean HTML input html
                        // strip all attributes
                        return preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $value);
                    }
                ]
            ],
            'binary' => [
                'txt' => [
                    'php' => function($value) {
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
                            if(strlen($res) > UPLOAD_MAX_FILE_SIZE) {
                                throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                            }
                        }
                        else {
                            if(!isset($value['tmp_name'])) {
                                // throw new \Exception("binary data has not been received or cannot be retrieved", QN_ERROR_UNKNOWN);
                                trigger_error("QN_DEBUG_ORM::binary data has not been received or cannot be retrieved", QN_REPORT_WARNING);
                                $res = '';
                            }
                            else {
                                if(isset($value['error']) && $value['error'] == 2 || isset($value['size']) && $value['size'] > UPLOAD_MAX_FILE_SIZE) {
                                    throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                                }
                                $res = file_get_contents($value['tmp_name']);
                            }
                        }

                        return $res;
                    }
                ],
                'sql'  => [
                    'php' => function($value) {
                        // read from DB
                        if(FILE_STORAGE_MODE == 'DB') {
                            // fields of type file/binary are stored as binary value (MEDIUMBLOB)
                            $res = $value;
                        }
                        // read the content from filestore, filename is the received value
                        else if(FILE_STORAGE_MODE == 'FS') {
                            $filename = $value;
                            if(strlen($filename) && file_exists(QN_BASEDIR.'/bin/'.$filename)) {
                                $res = file_get_contents(QN_BASEDIR.'/bin/'.$filename);
                            }
                            else {
                                throw new \Exception("binary_data_not_received", QN_ERROR_UNKNOWN);
                            }
                        }
                        return $res;
                    }
                ],
                'php' => [
                    'sql' => function() {
                        if(func_num_args() < 5) {
                            throw new \Exception("object_details_missing", QN_ERROR_MISSING_PARAM);
                        }
                        list($value, $class, $oid, $field, $lang) = func_get_args();

                        if(strlen($value) > UPLOAD_MAX_FILE_SIZE) {
                            throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                        }

                        // store binary data to database
                        if(FILE_STORAGE_MODE == 'DB') {
                            // convert to hexadecimal string and mark for being stored as binary
                            $res = 'h0x'.bin2hex($value);
                        }
                        // store binary data to a file which name is based on object details, and store filename to database
                        else if(FILE_STORAGE_MODE == 'FS') {
                            // build a unique name  `package/class/field/oid.lang`
                            $path = sprintf("%s/%s", str_replace('\\', '/', $class), $field);
                            $file = sprintf("%011d.%s", $oid, $lang);

                            $storage_location = realpath(QN_BASEDIR.'/bin').'/'.$path;

                            if (!is_dir($storage_location)) {
                                // make missing directories
                                FSManipulator::assertPath($storage_location);
                            }

                            if (!is_dir($storage_location)) {
                                throw new \Exception("fs_dir_missing", QN_ERROR_INVALID_CONFIG);
                            }
                            // note: existing file will be overwritten
                            file_put_contents($storage_location.'/'.$file, $value);

                            // return filename as result
                            $res = $path.'/'.$file;
                        }
                        return $res;
                    },
                    'txt' => function($value) {
                        return base64_encode($value);
                    }
                 ]
            ]

        ];
    }

	private function &getMethod($from, $to, $type) {
        if( !isset($this->config[$type][$from][$to]) ) {
            $this->config[$type][$from][$to] = function ($value) { return $value; };
        }
		return $this->config[$type][$from][$to];
	}

    /**
     * Override a given adaptation method.
     *
     *
     */
	public function setMethod($from, $to, $type, $method) {
        if(!is_callable($method)) {
            // warning QN_ERROR_INVALID_PARAM
        }
        else {
            // init related config if necessary
            if(!isset($this->config[$type])) {
                $this->config[$type] = [];
            }
            if(!isset($this->config[$type][$from])) {
                $this->config[$type][$from] = [];
            }
            $this->config[$type][$from][$to] = $method;
        }
        return $this;
	}


	public function adapt($value, $type, $to='php', $from='txt', ...$extra) {
        if($type == 'double') $type = 'float';
        else if($type == 'int') $type = 'integer';
        else if($type == 'bool') $type = 'boolean';
        else if($type == 'file') $type = 'binary';

        // #transition - accept json as txt
        if($from == 'json') {
            $from = 'txt';
        }
        if($to == 'json') {
            $to = 'txt';
        }

        if(!in_array($from, ['txt', 'php', 'sql'])) {
            // in case of unknown origin, fallback to raw text
            $from = 'txt';
            // todo: issue a warning
        }
        if(!in_array($to, ['txt', 'php', 'sql'])) {
            // in case of unknown destination, fallback to PHP
            $to = 'php';
            // todo: issue a warning
        }

        if( !is_null($this->config) && isset($this->config[$type][$from][$to]) ) {
            $method = $this->config[$type][$from][$to];
        }
        else {
            if(is_null($this->config)) $this->init();
            $method = &$this->getMethod($from, $to, $type);
        }

        return call_user_func_array( $method, array_merge([$value], $extra) );
	}

}