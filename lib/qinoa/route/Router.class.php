<?php
namespace qinoa\route;


use qinoa\organic\Service;

/**
 * 
 * Routes and resolutions consist of strings accepting optional ':' and '?' special chars
 *
 * JSON expected format:
 *
 * {
 *   "/test": {
 *       "GET": {
 *           "description": "Retrieve test data",
 *           "operation": "?get=mypackage_tests"
 *       } 
 *   }
 * } 
 *
 * Route structure (holding resolved route attributes)
 *  {
 *      method:         string      HTTP method
 *      path:           string      full path of the requested URI
 *      parts:          array       array of parts composing the path
 *      operation:      string      the operation the URI resolves to 
 *      params:         array       map of parameters names as keys, related to their values
 *  }
 */
class Router  extends Service {
    
    // routes providers
    private $providers;
    
    // resolved route
    private $route;

 
    public function __construct() {
        $this->providers = [];
        $this->route = [];        
    }
        
    // resolve all providers and return existing routes (first declaration prevails)
    public function getRoutes() {
        $routes = [];
        // process all providers until we get a match
        for($i = 0, $n = count($this->providers); $i < $n; ++$i) {
            $routes = array_merge($this->loadProvider($this->providers[$i]), $routes);
        }        
        return $routes;
    }
        
    private function loadProvider($provider) {
        $routes = [];
        // provider is an array of routes
        if(is_array($provider)) {            
            $routes = $provider;
        }
        // provider is a path to one or more JSON file
        else {
            $first = true;
            // loop amongst one or more files (matching wildcard if any)
            foreach(glob($provider) as $json_file) {
                // load content from first file
                if($first) {
                    if( ($json = @file_get_contents($json_file)) === false) throw new \Exception("routing config file $json_file not found", QN_ERROR_INVALID_CONFIG);    
                    if( ($routes = json_decode($json, true)) === null) throw new \Exception("malformed json in routing config file $json_file", QN_ERROR_INVALID_CONFIG);
                    $first = false;
                }
                else {
                    // store others as providers, if any
                    $this->add($json_file);
                }
            }
        }
        return $routes;
    }

    public static function normalizeOperation($operation) {
        $result = [];
        $op_params = [];        
        $query = explode('?', $operation);
        parse_str($query[1], $op_params);

        foreach($op_params as $type => $operation) {
            if(in_array(strtolower($type), ['do', 'get', 'show'])) {
                unset($op_params[$type]);
                $result = [
                    'name'      => $operation,
                    'type'      => strtolower($type),
                    'params'    => $op_params
                ];
                break;
            }
        }
        return $result;
    }
    
    public function normalize($path, $resolver) {
        $result = [];
        $routes = $this->loadRoute($path, $resolver);
        foreach($routes as $route) {
            if($route['operation']{0} == '/') {
                $route['redirect'] = $route['operation'];
                unset($route['operation']);                
            }
            else {
                $route['operation'] = self::normalizeOperation($route['operation']);
            }
            $result[] = $route;
        }
        return $result;
    }
    
    /**
     *
     * Convert an entry from a map (URI => resolver) to an array of route structures (stored as array)
     * Depending on the structure of resolver, we might return one or ore routes
     *
     */
    private function loadRoute($path, $resolver='') {
        $result = [];
        // get route components
        $route = [
            'method'    => 'GET',
            'path'      => $path,
            'parts'     => explode('/', ltrim($path, '/'))
        ];        
        // we have a match, check amongst 2 accepted formats: 
        // 1) URI => controller resolver (GET method implied)
        if(!is_array($resolver)) {
            // resolver is an URL
            $route['operation'] = $resolver;
            $result['GET'] = $route;            
        }
        // 2) URI => map METHOD / controller resolver
        else {
            foreach($resolver as $route_method => $route_details) {
                $route['method'] = $route_method;
                $route['operation'] = $route_details['operation'];
                $result[$route_method] = $route;
            }
        }
        return $result;
    }
    
    /**
     * Compare two Route structures
     *
     */
    private function match($path, $canvas) {
        $result = [];
        $n = count($canvas['parts']);
        $m = count($path['parts']);
        if($m > $n) {            
            $result = false;
        }
        else {
            // review each path component
            for($i = 0; $i < $n; ++$i) {
                // part is a parameter
                if(strlen($canvas['parts'][$i]) && $canvas['parts'][$i]{0} == ':') {                    
                    $is_mandatory = !(substr($canvas['parts'][$i], -1) == '?');
                    // we exceeded the number of parts in path
                    if($i >= $m) {
                        if($is_mandatory) {
                            $result = false;
                            break;
                        }
                    }
                    else {
                        $param = substr($canvas['parts'][$i], 1);
                        if(!$is_mandatory) $param = substr($param, 0, -1);
                        $result[$param] = $path['parts'][$i];
                    }
                }
                else {
                    // we exceeded the number of parts in path OR parts are distinct
                    if($i >= $m || strcmp($path['parts'][$i], $canvas['parts'][$i]) != 0) {
                        $result = false;                            
                        break;
                    }
                }
            }
        }
        return $result;
    }
    
    /**
    * Add a route collection provider 
    *
    * @param mixed (array | string) $provider   Either an associative array of routes or a path (or glob) to one or more JSON files
    *
    */    
    public function add($provider) {
        $this->providers[] = $provider;            
        return $this;
    }

    
    public function resolve($uri, $method='GET') {
        $path = current(($this->loadRoute($uri)));

        $lookup = function($routes) use($path, $method) {
            $found_url = null;
            // check routes and stop on first match
            foreach($routes as $route_path => $resolver) {
                $res = $this->loadRoute($route_path, $resolver);
                if(isset($res[$method])) {
                    if(($params = $this->match($path, $res[$method])) !== false) {
                        if(is_array($params)) {
                            $res[$method]['params'] = $params;
                            if($res[$method]['operation']{0} == '/') {
                                $res[$method]['redirect'] = $res[$method]['operation'];
                                unset($res[$method]['operation']);                
                            }
                            else {
                                $res[$method]['operation'] = self::normalizeOperation($res[$method]['operation']);
                            }
                        }
                        return $res[$method];
                    }
                }
            }
            return null;
        };
        
        // process all providers until we get a match
        $i = 0; 
        // we don't know the length of $this->providers beforehand (pushes can be made in $this->add())
        while($i < count($this->providers)) { 
            $routes = $this->loadProvider($this->providers[$i]);
            if($match = $lookup($routes)) {
                return $match;
            }
            ++$i;
        }
        return null;
    }    
    
}