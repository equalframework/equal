<?php
namespace equal\php;

use equal\organic\Service;
use equal\http\HttpRequest;
use equal\http\HttpResponse;


class Context extends Service {
    
    private $params;
    
    private $pid;
    
    private $time;
    
    private $httpRequest;
    
    private $httpResponse;   


    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     *
     */
    protected function __construct() {
        $this->params = [];
        // get PID from HTTP server / PHP cli
        $this->pid = getmypid();
        // get current unix time in microseconds
        $this->time = microtime();
        $this->httpRequest = null;
        $this->httpResponse = null;
    }

    public function __clone() {
        if($this->httpRequest)  $this->httpRequest = clone $this->httpRequest;
        if($this->httpResponse) $this->httpResponse = clone $this->httpResponse;
    }
    
    public function __toString() {
        return 'This is the PHP context instance';
    }
    
   
    public function get($var, $default=null) {
        if(isset($this->params[$var])) {
            return $this->params[$var];
        }
        return $default;
    }

    public function set($var, $value) {
        $this->params[$var] = $value;
        return $this;
    }
    
    public function getTime() {
        return $this->time;
    }
    
    public function getPid() {
        return $this->pid;
    }
    
    public function getHttpRequest() {
        if(is_null($this->httpRequest)) {
            // retrieve current request 
            $uri  = $this->getHttpUri();            
            $body = $this->getHttpBody($uri);
            $this->httpRequest = new HttpRequest($this->getHttpMethod().' '.$uri.' '.$this->getHttpProtocol(), $this->getHttpRequestHeaders(), $body);
        }
        return $this->httpRequest;
    }

    public function getHttpResponse() {
        if(is_null($this->httpResponse)) {
            // build response (set protocol to HTTP 1.1, status to success, content-type to 'application/json; charset=UTF-8', and retrieve default headers set by PHP)
            $this->httpResponse = new HttpResponse('HTTP/1.1 200 OK', $this->getHttpResponseHeaders());
        }
        return $this->httpResponse;
    }
    
    public function getSessionId() {
        return 0;
    }
    
    public function setHttpRequest(HttpRequest $request) {
        $this->httpRequest = $request;
        return $this;
    }

    public function setHttpResponse(HttpResponse $response) {
        $this->httpResponse = $response;
        return $this;
    }
    
    public function setSessionId(int $session_id) {        
        return $this;
    }    

    // below are additional method using short name and get/set based on arguments
    
    public function httpRequest() {
        $args = func_get_args();
        if(count($args) < 1) {
            return $this->getHttpRequest();
        }
        else {
            $request = $args[0];
            return $this->setHttpRequest($request);
        }        
    }

    public function httpResponse() {
        $args = func_get_args();
        if(count($args) < 1) {
            return $this->getHttpResponse();
        }
        else {
            $response = $args[0];
            return $this->setHttpResponse($response);
        }
    }
    
    // private methods
    
    
    private function getHttpResponseHeaders() {
        $res = [];
        $headers = headers_list();
        foreach($headers as $header) {
            list($name, $value) = explode(':', $header, 2);
            $res[$name] = trim($value);
        }
        $request_headers = $this->getHttpRequestHeaders();
        // set default content type to JSON and default charset to UTF-8
        $res['Content-Type'] = 'application/json; charset=UTF-8';        
        if(isset($request_headers['Accept'])) {
            // example: Accept: text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c
            $parts = explode(',', $request_headers['Accept']);
            $parts = explode(';', $parts[0]);
            $content_type = trim($parts[0]);
            if(strpos($request_headers['Accept'], '*/*') === false) {
                $res['Content-Type'] = $content_type;
            }
        }
        return $res;
    }
    
    /**
     *
     * HTTP message formatted protocol (i.e. HTTP/1.0 or HTTP/1.1)
     *
     */
    private function getHttpProtocol() {
        // default to HTTP 1.1
        $protocol = 'HTTP/1.1';
        if(isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        }
        return $protocol;
    }

    /** 
     * Retrieve the request method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     */    
    private function getHttpMethod() {
        static $method = null;        
        if(!$method) {
            // fallback to GET method (using CLI, REQUEST_METHOD is not set), i.e. 'GET'
            $method = 'GET';
            if(isset($_SERVER['REQUEST_METHOD'])) {
                $method = $_SERVER['REQUEST_METHOD'];
                if (strcasecmp($method, 'POST') === 0) {
                    if (isset($_SERVER['X-HTTP-METHOD-OVERRIDE'])) {
                        $method = $_SERVER['X-HTTP-METHOD-OVERRIDE'];
                    }
                }
                // normalize to upper case
                $method = strtoupper($method);                
            }
        }
        return $method;        
    }
    
    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     */    
    private function getHttpRequestHeaders() {
        static $headers = null;
        
        if(!$headers) {
            // 1) retrieve headers
            if (function_exists('getallheaders')) {
                $headers = (array) getallheaders();                  
            }
            else {
                // Polyfill from https://github.com/ralouphie/getallheaders
                // Copyright (c) 2014 Ralph Khattar - License: The MIT License (MIT)                            
                $headers = array();
                $copy_server = array(
                    'CONTENT_TYPE'   => 'Content-Type',
                    'CONTENT_LENGTH' => 'Content-Length',
                    'CONTENT_MD5'    => 'Content-MD5',
                );
                // Convert back headers added by PHP with `HTTP_` prefix to their original name (origin, referer, host, user-agent, accept-language, accept-encoding, accept-charset)
                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) === 'HTTP_') {
                        $key = substr($key, 5);
                        if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                            $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                            $headers[$key] = $value;
                        }
                    } elseif (isset($copy_server[$key])) {
                        $headers[$copy_server[$key]] = $value;
                    }
                }              
            }

            // 2) normalize headers
            // handle origin header
            if (!isset($headers['Origin'])) {
                if(isset($headers['Host'])) {
                    $scheme = isset($_SERVER['HTTPS']) ? "https" : "http";
                    $headers['Origin'] = $scheme.'://'.$headers['Host'];
                }
                else if(isset($headers['Referer'])) {
                    if ( $parts = parse_url( $headers['Referer'] ) ) {
                        $headers['Origin'] = $parts[ "scheme" ].'://'.$parts[ "host" ];
                    }
                }
            }
            // handle Authorization header
            if (!isset($headers['Authorization'])) {
                if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                } 
                elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                    $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                    $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
                } 
                elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                    $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
                }
            }              
            // handle ETags
            if(!isset($headers['ETag'])) {
                if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
                    $headers['ETag'] = $_SERVER['HTTP_IF_NONE_MATCH'];
                }
                else {
                    $headers['ETag'] = '';
                }
            }
            // handle client's IP address : we make sure that 'X-Forwarded-For' is always set with the most probable client IP
            // fallback to localhost (using CLI, REMOTE_ADDR is not set), so we default to '127.0.0.1'
            $client_ip = '127.0.0.1';
            if(isset($_SERVER['REMOTE_ADDR'])) {
                $client_ip = $_SERVER['REMOTE_ADDR'];
            }            
            if(!isset($headers['X-Forwarded-For'])) {
                $headers['X-Forwarded-For'] = $client_ip;
            }
            // assign X-Forwarded-For with a single IP (first in list)
            $headers['X-Forwarded-For'] = stristr($headers['X-Forwarded-For'],',') ? stristr($headers['X-Forwarded-For'],',',true) : $headers['X-Forwarded-For'];
        }
        if(isset($headers['content-type'])) {
            $headers['Content-Type'] = $headers['content-type'];
        }
        // adapt Content-Type for multipart/form-data (already parsed by PHP)
        if(isset($headers['Content-Type'])) {
            if($this->getHttpMethod() == 'POST' && strpos($headers['Content-Type'], 'multipart/form-data') === 0) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }
        else {
            // will be parsed using parse_str
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        return $headers;
    }
    
    private function getHttpUri() {
        $scheme = isset($_SERVER['HTTPS']) ? "https" : "http";
        $auth = '';
        if(isset($_SERVER['PHP_AUTH_USER']) && strlen($_SERVER['PHP_AUTH_USER']) > 0) {
            $auth = $_SERVER['PHP_AUTH_USER'];
            if(isset($_SERVER['PHP_AUTH_PW']) && strlen($_SERVER['PHP_AUTH_PW']) > 0) {
                $auth .= ':'.$_SERVER['PHP_AUTH_PW'];
            }
            $auth .= '@';
        }
        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
        $port = isset($_SERVER['SERVER_PORT'])?$_SERVER['SERVER_PORT']:80;
        // fallback to current script name (using CLI, REQUEST_URI is not set), i.e. '/index.php'
        $uri = '/'.$_SERVER['SCRIPT_NAME'];

        if(isset($_SERVER['REQUEST_URI'])) {
            if(substr($_SERVER['REQUEST_URI'], 0, 1) == '/') {
                // relative URI
                $uri = $_SERVER['REQUEST_URI'];
            }
            else {
                // absolute URI
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH).'?'.parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            }
        }
        else if(php_sapi_name() === 'cli' || defined('STDIN')) {
            $args = [];
            // follow getopt long options specs (http://www.gnu.org/software/libc/manual/html_node/Getopt-Long-Options.html)
            for($i = 1; $i < $_SERVER['argc']; ++$i) {
                if(strpos($_SERVER['argv'][$i], '--') === 0) {
                    $parts = explode('=', substr($_SERVER['argv'][$i], 2));
                    $param = $parts[0];
                    $value = (isset($parts[1]))?$parts[1]:null;
                    // handle array notation (a name followed by brackets)
                    if(preg_match("/(.*)\[(.*)\]/i", $param, $matches)) {                        
                        $param = $matches[1];
                        if(!isset($args[$param])) {
                            $args[$param] = [];
                        }
                        if(strlen($matches[2])) {
                            $args[$param][$matches[2]] = $value;
                        }
                        else $args[$param][] = $value;
                    }
                    else {
                        $args[$param] = $value;
                    }                    
                }
            }
            $uri .= '?'.http_build_query($args);
        }
        return $scheme."://".$auth."$host:$port{$uri}";
    }

    private static function normalizeFiles() {
        $normalized_files = [];        
        if(count($_FILES) == 1) {
            return $_FILES;
        }
        foreach($_FILES as $index => $file) {
            if (!is_array($file['name'])) {
                $normalized_files[$index][] = $file;
                continue;
            }
            foreach($file['name'] as $idx => $name) {
                $normalized_files[$index][$idx] = [
                    'name'      => $file['name'][$idx],
                    'type'      => $file['type'][$idx],
                    'error'     => $file['error'][$idx],
                    'size'      => $file['size'][$idx],
                    'tmp_name'  => $file['tmp_name'][$idx]
                ];
            }
        }
        return $normalized_files;        
    }

    private static function getMaxMemory() {
        function to_bytes($val) {
            $val = strtolower(trim($val));
            $chars = str_split($val);
            $suffix = array_pop($chars);
            switch($suffix) {
                case 'g': return intval($val) * 1024 * 1024 * 1024;
                case 'm': return intval($val) * 1024 * 1024;
                case 'k': return intval($val) * 1024;
            }
            return intval($val);
        }
        return min(to_bytes(ini_get('upload_max_filesize')), to_bytes(ini_get('post_max_size')));
    }

    private function getHttpBody($uri='') {
        $body = '';
        // in case script was invoked by CLI
        if(php_sapi_name() === 'cli' || defined('STDIN')) {
            // Windows does not support non-bloking reading from STDIN
            $OS = strtoupper(substr(PHP_OS, 0, 3));
            $options = getopt('f::');
            // so we disable auto-feed from stdin unless there is a 'f' args (to force it)
            if ( $OS !== 'WIN' || isset($options['f'])) {
                // fetch body from stdin
                $body = '';
                $stdin = fopen('php://stdin', "r");
                $read  = array($stdin);
                $write = null;
                $except = null;
                if ( stream_select( $read, $write, $except, 0 ) === 1 ) {
                    while ($line = fgets( $stdin )) {
                        $body .= $line;
                    }
                }
            }
        }
        else {
            // load raw content from input stream
            // careful here: in case raw data exceed `upload_max_filesize` or `post_max_size`, stream `php://input` might contain more data than available memory
            $max = self::getMaxMemory() - memory_get_usage() - 1;
            if($max > 0) {
                $raw = file_get_contents('php://input', false, null, 0, $max+1);
                if(strlen($raw) > $max) {
                    throw new \Exception("received data exceed maximum available size", QN_ERROR_NOT_ALLOWED);
                }
                // #memo : this function is impacted by ini_get('max_input_vars')
                parse_str($raw, $body);    
            }
        }
        // in some cases, PHP consumes the input to populate $_REQUEST and $_FILES (i.e. with multipart/form-data content-type)
        if(empty($body) || (is_string($body) && strlen($body) < 3) ) { // we could have received a formatted empty body (e.g.: {})
            // for GET methods, PHP improperly fills $_GET and $_REQUEST with query string parameters
            // we allow this only when there's nothing from php://input
            if(isset($_FILES) && !empty($_FILES)) {
                $body = array_merge_recursive($_REQUEST, self::normalizeFiles());
            }
            else {
                $body = $_REQUEST;                
            }
            // mimic PHP behaviour: inject query string args to the body (only for args not already present in the body)
            if(strlen($uri)) {
                $parts = parse_url($uri);
                if($parts != false && isset($parts['query'])) {
                    parse_str($parts['query'], $params);    
                    foreach($params as $param => $value) {
                        if(!isset($body[$param])) {
                            $body[$param] = $value;
                        }
                    }    
                }
            }
            // we should have set content-type accordingly while retrieving headers (@see getHttpRequestHeaders())
        }
        return $body;
    }    
}