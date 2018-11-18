<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\http;

/**
 *
 *
 *
 *  @see class UriHelper
 *
 *  getters :   scheme, user, password, host, port, path, query, fragment
 */
class HttpUri {

/**
 *
 *
 *                         hierarchical part
 *           ┌────────────────────┴────────────────────┐
 *                         authority             path
 *           ┌────────────────┴──────────────┐┌────┴───┐
 *     abc://username:password@example.com:123/path/data?key=value&key2=value2#fragid
 *     └┬┘   └──────┬────────┘ └────┬────┘ └┬┘           └─────────┬─────────┘ └─┬──┘
 *   scheme  user information      host    port                  query        fragment
 *
 */

 
    private $parts = null;
    
    public function __construct($uri='') {
        // init $parts member to allow further methods calls even if provided URI is not valid
        $this->parts = [
            'scheme'    => null,
            'host'      => null,
            'port'      => null,
            'path'      => null,
            'query'     => null,
            'fragment'  => null,
            'user'      => null,
            'pass'      => null
        ];
   
        $this->setUri($uri);
    }    
    
    
    /**
     *
     * re-build final URI from parts
     * @return string
     */
    public function __toString() {
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        if(strlen($fragment) > 0) {
            $query = $query.'#'.$fragment;
        }
        if(strlen($query) > 0) {
            $query = '?'.$query;
        }
        return $this->getScheme().'://'.$this->getAuthority().$this->getPath().$query;
    }

    // retrieve parameters associative array from current query string    
    public function getParams() {
        $params = [];
        // retrieve parameters associative array from current query string
        parse_str($this->getQuery(), $params);
        return $params;
    }
    
    /**
     * Get the value of specified param from the query string, fallback to $default if not found.
     *
     *
     * @return mixed If $param is an array of parameters names, returns an assiociative array containing values for each given parameter, otherwise returns the value of a single parameter. If given parameter is not found, returns specified default value (fallback to null)     
     */
    public function get($param, $default=null) {
        // retrieve parameters associative array from current query string        
        $params = $this->getParams();
        // bulk get : $param is an array of parameters names
        if(is_array($param)) {
            $res = [];
            foreach($param as $p) {
                if(isset($params[$p])) {
                    $res[$p] = $params[$p];
                }
                else {
                    $res[$p] = null;
                }
            }
        }
        // $param is a single parameter name
        else {
            $res = $default;
            if(isset($params[$param])) $res = $params[$param];
        }
        return $res;        
    }

    /**
     * Assign a new parameter to URI query string, or update an existing one to a new value.
     * This method overwrites existing parameter(s) from query string, if any.
     * 
     * @param   $param  mixed(string|array)    For single assignement, $param is the name of the parameter to be set. In case of bulk assign, $param is an associative array with keys and values respectively holding parameters names and values.
     * @param   $value  mixed   If $param is an array, $value is not taken under account (this argument is therefore optional)
     * @return  HttpUri Returns current instance
     */    
    public function set($param, $value=null) {    
        $params = [];
        // retrieve parameters associative array from current query string
        parse_str($this->getQuery(), $params);
        // update parameters associative array
        if(is_array($param)) {
            foreach($param as $p => $val) {
                $params[$p] = $val;
            }
        }
        else {
            $params[$param] = $value;
        }
        // update query string
        $this->setQuery(http_build_query($params)); 
        return $this;
    }
    
    public function setUri($uri) {
        if(self::isValid($uri)) {
            $this->parts = parse_url($uri);
        }
        return $this;
    }

    public function setScheme($scheme) {
        $this->parts['scheme'] = $scheme;
        return $this;
    }
    
    public function setHost($host) {
        $this->parts['host'] = $host;
        return $this;
    }

    public function setPort($port) {
        $this->parts['port'] = $port;
        return $this;
    }

    public function setPath($path) {
        $this->parts['path'] = $path;
        return $this;
    }

    public function setQuery($query) {
        $this->parts['query'] = $query;
        return $this;
    }

    public function setFragment($fragement) {
        $this->parts['fragment'] = $fragment;
        return $this;
    }
    
    public function setUser($user) {
        $this->parts['user'] = $user;
        return $this;
    }

    public function setPassword($password) {
        $this->parts['pass'] = $password;
        return $this;
    }
    
    /**
     * Checks validity of provided URI
     * with support for internationalized domain name (IDN) support (non-ASCII chars)
     *
     * @param $uri  string
     */
    public static function isValid($uri) {
        $res = filter_var($uri, FILTER_VALIDATE_URL);
        if (!$res) {
            // check if uri contains unicode chars
            $mb_len = mb_strlen($uri);
            if ($mb_len !== strlen($uri)) {
                // replace all multi-bytes chars with a single-byte char (A)
                $safe_uri = '';
                for ($i = 0; $i < $mb_len; ++$i) {
                    $ch = mb_substr($uri, $i, 1);
                    $safe_uri .= strlen($ch) > 1 ? 'A' : $ch;
                }
                // re-check normalized URI
                $res = filter_var($safe_uri, FILTER_VALIDATE_URL);
            }
        }
        return $res;
    }


    /**
     *
     *
     * @example http, https, ftp
     */
    public function getScheme() {
        return isset($this->parts['scheme'])?$this->parts['scheme']:'';
    }
    
    public function getHost() {
        return isset($this->parts['host'])?$this->parts['host']:'';
    }

    public function getPort() {
        static $standard_ports = [
            'ftp'   => 21,
            'sftp'  => 22,
            'ssh'   => 22,
            'http'  => 80,
            'https' => 443
        ];
        $scheme = $this->getScheme();
        $default_port = isset($standard_ports[$scheme])?$standard_ports[$scheme]:'';
        return isset($this->parts['port'])?$this->parts['port']:$default_port;
    }

    public function getAuthority() {
        $user_info = '';
        if(isset($this->parts['user']) && strlen($this->parts['user']) > 0) {
            $user_info = $this->parts['user'];
            if(isset($this->parts['pass']) && strlen($this->parts['pass']) > 0) {
                $user_info .= ':'.$this->parts['pass'];
            }
            $user_info .= '@';
        }
        return $user_info.$this->getHost().':'.$this->getPort();
    }
    
    public function getPath() {
        return isset($this->parts['path'])?$this->parts['path']:'';
    }

    public function getQuery() {
        return isset($this->parts['query'])?$this->parts['query']:'';
    }

    public function getFragment() {
        return isset($this->parts['fragment'])?$this->parts['fragment']:'';
    }
    
    public function getUser() {
        return isset($this->parts['user'])?$this->parts['user']:'';
    }

    public function getPassword() {
        return isset($this->parts['pass'])?$this->parts['pass']:'';
    }

    public function getBasePath() {
        return str_replace(DIRECTORY_SEPARATOR, '/', dirname($this->parts['path']));        
    }

    public function query() {
        $args = func_get_args();
        if(count($args) < 1) {
            return $this->getQuery();
        }
        else {
            $query = $args[0];
            return $this->setQuery($query);
        }
    }    
}