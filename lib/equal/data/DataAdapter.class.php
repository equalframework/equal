<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data;

use equal\organic\Service;
use equal\fs\FSManipulator;
use equal\html\HTMLPurifier;
use equal\html\HTMLPurifier_Config;

class DataAdapter extends Service {
    
    private $config;

    protected function __construct(/* no dependency */) {
        // initial configuration
        $this->config = null;
    }
    
    private function init() {
        $this->config = [
            'boolean' => [
                'txt' => [
                    'php' =>    function ($value) {
                                    if(in_array($value, ['TRUE', 'true', '1', 1, true], true)) return true;
                                    else if(in_array($value, ['FALSE', 'false', '0', '', 0, false], true)) return false;
                                    return $value;
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
                                    if(is_string($value)) {
                                        if(in_array($value, ['TRUE', 'true'])) $value = 1;
                                        else if(in_array($value, ['FALSE', 'false'])) $value = 0;
                                    }
                                    // arg represents a numeric value (numeric type or string)
                                    if(is_numeric($value)) {
                                        $value = intval($value);
                                    }
                                    return $value;
                                }
                ],
                'sql' => [
                    'php' =>    function ($value) {
// todo : handle arbitrary length numbers (> 10 digits)
                                    return intval($value);
                                }
                ]                
            ],            
            'double' => [
                'txt'   => [
                    'php' =>    function ($value) {
                                    // arg represents a numeric value (numeric type or string)
                                    if(is_numeric($value)) {
                                        $value = floatval($value);
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
                                    // #memo urldecoding (and any pre-treatment) should not occur here
                                    return $value;
                                }
                ]
            ],
            // dates are handled as unix timestamp
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
                                    }
                                    return $value;
                                }
                ],
                // internally, we handle dates as timestamps
                'php'   => [
                    'txt' =>    function ($value) {
                                    // return date as a ISO 8601 formatted string
                                    return date("c", $value);
                                },
                    'sql' =>    function ($value) {
                                    return date('Y-m-d', $value);
                                },
// todo : deprecate
                    'orm' =>    function ($value) {
                                    return date('Y-m-d', $value);
                                }                                      
                ],
                'sql'   => [
                    'php' =>    function ($value) {
                                    // return date as a timestamp
                                    list($year, $month, $day) = sscanf($value, "%d-%d-%d");
                                    return mktime(0, 0, 0, $month, $day, $year);
                                }
                ],
// todo : deprecate                
                'orm' => [
                    'php' =>    function ($value) {
                                    // return date as a timestamp
                                    list($year, $month, $day) = sscanf($value, "%d-%d-%d");
                                    return mktime(0, 0, 0, $month, $day, $year);                                    
                                }
                ]
            
            ],            
            'array' => [
                'txt'   => [
                    'php' =>    function ($value) {
                                    if(!is_array($value)) {
                                        if(empty($value)) $value = array();
                                        // adapt raw CSV
                                        else $value = explode(',', $value);
                                    }
                                    return $value;
                                }
                ]                
            ],
            'many2one'  => [
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
                ]
            ],
            'many2many' => [
                'txt' => [
                    'php' =>    function($value) {
                                    // we got a string: convert to array
                                    if(is_string($value)) {
                                        $value = explode(',', $value);
                                    }
                                    else if(!is_array($value)) {
                                        $value = (array) $value;
                                    }
                                    // check for recursion
                                    if(is_array($value[0])) {
                                        $value = array_map(function($a) { return isset($a['id'])?$a['id']:'?'; }, $value);
                                    }
                                    // make sure array contains only numbers or numeric strings
                                    $value = array_filter($value, function ($a) { return is_numeric($a); });
                                    return $value;
                                }
                ]
            ],
            'html' => [
                'txt' => [
                    'php' => function ($value) {
                        // clean HTML input html
                        // standard cleaning: remove non-standard tags and attributes    
                        $purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());    
                        return $purifier->purify($value);                        
                    }
                ]
            ],
            'file' => [
                'txt' => [
                    'php' => function($value) {                       
                        /* 
                         $value is expected to be either a base64 string 
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
                            // @see RFC2397 (data:[<mediatype>][;base64],<data>)
                            $value = str_replace(' ', '+', substr($value, strpos($value,',')+1) );
                            $res = base64_decode($value);
                        }
                        else {
                            if(!isset($value['tmp_name'])) {
                                // throw new \Exception("binary data has not been received or cannot be retrieved", QN_ERROR_UNKNOWN);                    
                                // todo: issue a warning                                
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
                        // convert hexadecimal string read from DB to a binary value
                        if(FILE_STORAGE_MODE == 'DB') {
                            $res = hex2bin($value);
                        }
                        // read the content from filestore, filename is the received value
                        else if(FILE_STORAGE_MODE == 'FS') {
                            $filename = $value;
                            if(strlen($filename) && file_exists(FILE_STORAGE_DIR.'/'.$filename)) {
                                $res = file_get_contents(FILE_STORAGE_DIR.'/'.$filename);
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

                        if(strlen($res) > UPLOAD_MAX_FILE_SIZE) {
                            throw new \Exception("file_exceeds_maximum_size", QN_ERROR_NOT_ALLOWED);
                        }

                        // store binary data to database as an hexadecimal string
                        if(FILE_STORAGE_MODE == 'DB') {
                            $res = bin2hex($value);
                        }
                        // store binary data to a file which name is based on object details, and store filename to database
                        else if(FILE_STORAGE_MODE == 'FS') {
                            // build a unique name  `package/class/field/oid.lang`
                            $path = sprintf("%s/%s", str_replace('\\', '/', $class), $field);
                            $file = sprintf("%011d.%s", $oid, $lang);                       
                                                    
                            $storage_location = realpath(FILE_STORAGE_DIR).'/'.$path;
    
                            if (!is_dir($storage_location)) {
                                // make missing directories
                                FSManipulator::assertPath($storage_location);
                            }
                            
                            if (!is_dir($storage_location)) {
                                throw new \Exception("unable to store file (check FS permissions)", QN_ERROR_INVALID_CONFIG);
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
            ],
            // dates are handled as unix timestamp
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
                                    }
                                    return $value;
                                }
                ],
                'php'   => [
                    'txt' =>    function ($value) {
                                    // return date as a ISO 8601 formatted string
                                    return date("c", $value);
                                },
                    'sql' =>    function ($value) {
                                    return date('Y-m-d H:i:s', $value);
                                }
                ],
                'sql'   => [
                    'php' =>    function ($value) {
                                    // return SQL date as a timestamp
                                    list($year, $month, $day, $hour, $minute, $second) = sscanf($value, "%d-%d-%d %d:%d:%d");
                                    return mktime($hour, $minute, $second, $month, $day, $year);
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
     *
     * configuration might be update by external scripts or providers
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
        if($type == 'float') $type = 'double';
        else if($type == 'int') $type = 'integer';
        else if($type == 'bool') $type = 'boolean';
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
            $method = &self::getMethod($from, $to, $type);
        }

        return call_user_func_array( $method , array_merge([$value], $extra) );
	}
    
}