<?php
/**
*    This file is part of the Qinoa framework.
*    https://github.com/cedricfrancoys/qinoa
*
*    Some Rights Reserved, Cedric Francoys, 2017, Yegen
*    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace {
	define('__QN_LIB', true) or die('fatal error: __QN_LIB already defined or cannot be defined');

    define('QN_BASE_DIR', dirname(__FILE__));
    
	/**
	* Add some global system-related constants (those cannot be modified by other scripts)
	*/
    include_once('config/config.inc.php');

    

	/** 
	* Add some config-utility functions to the global namespace
	*/

	/**
	* Returns a configuraiton parameter.
	*/
	function config($name, $default=null) {
        return (defined($name))?constant($name):$default;
	}

	/**
	* Force the script to be either silent (no output) or verbose (according to DEBUG_MODE).
	* @param boolean $silent
	*/
	function set_silent($silent) {
		$GLOBALS['SILENT_MODE'] = $silent;
        /*
		ini_set('display_errors', !$silent);
		if($silent) error_reporting(0);
		else error_reporting(E_ALL);
        */
	}

}
namespace config {
    use qinoa\services\Container;
    

    
	/**
	* Add some config-utility functions to the 'config' namespace
	*/

	/**
	* Adds a parameter to the configuration array
	*/	
	function define($name, $value) {
		$GLOBALS['QN_CONFIG_ARRAY'][$name] = $value;
	}

	/**
	* Checks if a parameter has already been defined
	*/	
	function defined($name) {
		return \defined($name) || isset($GLOBALS['QN_CONFIG_ARRAY'][$name]);
	}


    /**
    *
    * can be invoked in local config files to override a specific service (used by the core services)
    * same global array is used by the service container
    *
    */    
    function register($name, $class=null) {
        if(!isset($GLOBALS['QN_SERVICES_POOL'])) {
            $GLOBALS['QN_SERVICES_POOL'] = [];
        }
        if(is_array($name)) {
            foreach($name as $service => $class) {
                $GLOBALS['QN_SERVICES_POOL'][$service] = $class;
            }
        }
        else {
            $GLOBALS['QN_SERVICES_POOL'][$name] = $class;
        }
    }

	/**
	* Returns a configuraiton parameter.
	*/
	function config($name, $default=null) {
		return (isset($GLOBALS['QN_CONFIG_ARRAY'][$name]))?$GLOBALS['QN_CONFIG_ARRAY'][$name]:$default;
	}
	
	/**
	* Export parameters declared with config\define function, as constants (i.e.: accessible through global scope)
	*/
	function export_config() {
		if(!isset($GLOBALS['QN_CONFIG_ARRAY'])) $GLOBALS['QN_CONFIG_ARRAY'] = array();
		foreach($GLOBALS['QN_CONFIG_ARRAY'] as $name => $value) {
			\defined($name) or \define($name, $value);
			unset($GLOBALS['QN_CONFIG_ARRAY'][$name]);
		}
	}

	class QNlib {
        
		/**
		* Adds the library folder to the include path (library folder should contain the Zend framework if required)
		*
		* @static
		*/
		public static function init() {
            // allow inclusion and autoloading of external classes
            if(file_exists(QN_BASE_DIR.'/vendor/autoload.php')) {            
                include_once(QN_BASE_DIR.'/vendor/autoload.php');
            }
            
            // register own class loader
            spl_autoload_register(__NAMESPACE__.'\QNlib::load_class');
            
            // check service container availability
            if(!is_callable('qinoa\services\Container::getInstance')) {
                throw new \Exception('Qinoa init: mandatory Container service is missing or cannot be instanciated', QN_REPORT_FATAL);
            }
            // instanciate service container
            $container = Container::getInstance();
            
            // register names for common services and assign default classes
            $container->register([
                'report'    => 'qinoa\error\Reporter',
                'auth'      => 'qinoa\auth\AuthenticationManager', 
                'access'    => 'qinoa\access\AccessController', 
                'context'   => 'qinoa\php\Context', 
                'validate'  => 'qinoa\data\DataValidator', 
                'adapt'     => 'qinoa\data\DataAdapter', 
                'orm'       => 'qinoa\orm\ObjectManager',
                'route'     => 'qinoa\route\Router'
            ]);
            
            // make sure mandatory dependencies are available (reporter requires context)
            try {
                $container->get(['report', 'context']);
            }
            catch(\Exception $e) {
                // fallback to a manual HTTP 500 
                header("HTTP/1.1 503 Service Unavailable");
                header('Content-type: application/json; charset=UTF-8');
                echo json_encode([
                    'errors' => ['UNKNOWN_SERVICE' => $e->getMessage()]
                 ], 
                 JSON_PRETTY_PRINT);
                // and raise an exception (will be output in PHP error log)                
                throw new \Exception("Qinoa init: a mandatory dependency is missing or cannot be instanciated", QN_REPORT_FATAL);
            }
            // register ORM classes autoloader, if any
            try {
                $om = $container->get('orm');
                // init collections provider
                $collect = $container->get('qinoa\orm\Collections');
                spl_autoload_register([$om, 'getStatic']);                            
            }
            catch(\Exception $e) {}
            
		}

		/**
		* Gets the complete URL (uniform resource locator)
		*
		* @static
		* @param	boolean $server_port	display server port (required when differs from 80)
		* @param	boolean	$query_string	display query_string (i.e.: script.php?...&...&...)
		* @return	string
		*/
// todo : deprecate        
		public static function get_url($server_port=true, $query_string=true) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
			$url = $protocol.$_SERVER['SERVER_NAME'];
			if($server_port && $_SERVER['SERVER_PORT'] != '80')  $url .= ':'.$_SERVER['SERVER_PORT'];
			// add full request URI if required
			if($query_string) $url .= $_SERVER['REQUEST_URI'];	
			// otherwise get the base directory of the current script (assuming this script is located in the root installation dir)
			else $url .= substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
			return $url;
		}


		/**
         * Retrieve, adapt and validate expected parameters from the HTTP request and provide requested dependencies.
         * Also ensures that required parameters have been transmitted and sets default values for missing optional params.
         *
		 * Accepted types for parameters types are PHP basic types: int, bool, float, string, array
		 * Note: un-announced parameters in HTTP request are ignored (dropped)
         *
		 * @static
		 * @param	array	$announcement	Array holding the description of the script and its parameters. 
		 * @return	array	parameters and their final values
         * @throws Exception raises an exception if: a dependency failed to load, OR a mandatory param is missing OR a param has invalid value
		 */
		public static function announce(array $announcement) {		
            // 0) init vars
			$result = array();
			$mandatory_params = array();
            // retrieve service container
            $container = Container::getInstance();
            // retrieve required services    
            list($context, $reporter, $dataValidator, $dataAdapter) = $container->get(['context', 'report', 'validate', 'adapt']);
            // fetch body and method from HTTP request 
            $request = $context->httpRequest();
            $body = (array) $request->body();
            $method = $request->method();
            if(isset($announcement['response'])) {
                $response = $context->httpResponse();
                if(isset($announcement['response']['content-type'])) {
                    $response->headers()->setContentType($announcement['response']['content-type']);
                }
                if(isset($announcement['response']['charset'])) {
                    $response->headers()->setCharset($announcement['response']['charset']);
                }
                if(isset($announcement['response']['accept-origin'])) {
                    $response->header('Access-Control-Allow-Origin', $announcement['response']['accept-origin']);
                    $response->header('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD,TRACE');
                    $response->header('Access-Control-Allow-Headers', '*');
                    $response->header('Access-Control-Expose-Headers', '*');
                    $response->header('Allow', '*');
                }                
            }
            // normalize $announcement array
			if(!isset($announcement['params'])) $announcement['params'] = array();
            
            // 1) check if all required parameters have been received
            
            // build mandatory fields array
			foreach($announcement['params'] as $param => $config) {
				if(isset($config['required']) && $config['required']) $mandatory_params[] = $param;
            }
			// if at least one mandatory param is missing
            $missing_params = array_values(array_diff($mandatory_params, array_keys($body)));
			// if(	count(array_intersect($mandatory_params, array_keys($body))) != count($mandatory_params) 
            if( count($missing_params) || isset($body['announce']) || $method == 'OPTIONS') {
                if(count($missing_params)) {
                    // no feedback about services
                    if(isset($announcement['providers'])) unset($announcement['providers']);
                    // no feedback about constants
                    if(isset($announcement['constants'])) unset($announcement['constants']);                    
                }
                // add announcement to response body
                $context->httpResponse()->body(['announcement' => $announcement]);
                if(isset($body['announce']) || $method == 'OPTIONS') {
                    $context->httpResponse()
                    ->status(200)
                    ->header('Content-Type', 'application/json')
                    ->send();
                    throw new \Exception('', 0);
                }
                // raise an exception with error details
                throw new \Exception(implode(',', $missing_params), QN_ERROR_MISSING_PARAM);
			}
		
			// 2) find any missing parameters
            
			$allowed_params = array_keys($announcement['params']);
            $invalid_params = array_diff(array_keys($body), $allowed_params);
            foreach($invalid_params as $invalid_param) {
                $reporter->warning("dropped unexpected parameter '{$invalid_param}'");
                unset($body[$invalid_param]);
            }
			$missing_params = array_diff($allowed_params, array_intersect($allowed_params, array_keys($body)));

			// 3) build result array and set default values for optional missing parameters
            
			foreach($announcement['params'] as $param => $config) {
                // note : at some point condition had a clause " || empty($body[$param]) ", remember not to alter received data
				if(in_array($param, $missing_params) && isset($config['default'])) {
                    $body[$param] = $config['default'];
				}
                if(array_key_exists($param, $body)) {
                    // prevent type confusion while converting data from text
                    // all inputs are handled as text, conversion is made based on expected type
                    $result[$param] = $dataAdapter->adapt($body[$param], $config['type']);
                }
			}

            // 4) validate values types and handle optional attributes
            
            $invalid_params = [];
            foreach($result as $param => $value) {
                $config = $announcement['params'][$param];
                // build constraints array
                $constraints = [];
                // adapt type to match PHP internals
                $constraints[] = ['kind' => 'type', 'rule' => $config['type']];
                // append explicit constraints
                foreach(array('min', 'max', 'in', 'not in') as $constraint) {
                    if(isset($config[$constraint])) {
                        $constraints[] = ['kind' => $constraint, 'rule' => $config[$constraint]];
                    }
                }
                // validate parameter's value
                if(!$dataValidator->validate($value, $constraints)) {
                    if(!in_array($param, $mandatory_params)) {
                        $reporter->warning("dropped invalid non-mandatory parameter '{$param}'");
                        unset($result[$param]);
                    }
                    else $invalid_params[] = $param;
                }
            }
            if(count($invalid_params)) {
                // no feedback about services
                if(isset($announcement['providers'])) unset($announcement['providers']);
                // add announcement to response body
                $context->httpResponse()->body(['announcement' => $announcement]);                
                // raise an exception with error details
                throw new \Exception(implode(',', $invalid_params), QN_ERROR_INVALID_PARAM);
            }            
            
            // 5) check for requested providers
            
            if(isset($announcement['providers']) && count($announcement['providers'])) {
                $providers = [];
                // inject dependencies
                foreach($announcement['providers'] as $key => $name) {
                    // handle custom name maping
                    if(!is_numeric($key)) {
                        $container->register($key, $name);
                        $name = $key;
                    }
                    $providers[$name] = $container->get($name);
                }
                // set result as an array holding params and providers
                $result = [$result, $providers];                
            }
            
            // 6) check for requested constants

            if(isset($announcement['constants']) && count($announcement['constants'])) {
                // inject dependencies
                foreach($announcement['constants'] as $name) {
                    if(!defined($name)) {
                        throw new \Exception("Requested constant {$name} is missing from configuration", QN_ERROR_INVALID_CONFIG);
                    }
                }
            }
            
			return $result;
		}

        private static function resolveRoute(string $uri) {
        }
           
        /**
         * Execute a given operation.
         *
         * This method stacks the context, sets URI and body according to arguments, and calls the targeted script.
         . In case the operation is not defined, a QN_ERROR_UNKNOWN_OBJECT error is raised (HTTP 404)
         *
         * @param $type
         * @param $operation
         * @param $body
         * @param $root
         *         
         * @example run('get', 'public:resiway_tests', ['test'=> 1])
         */
		public static function run($type, $operation, $body=[], $root=false) {
            trigger_error("DEBUG_ORM::calling run method for $type:$operation", QN_REPORT_DEBUG);
            $result = '';
            $resolved = [
                'type'      => $type,       // 'do', 'get' or 'show'
                'operation' => null,        // {package}_{script_path}
                'visibility'=> 'public',    // 'public' or 'private'
                'package'   => null,        // {package}   
                'script'    => null         // {path/to/script.php}
            ];
            // define valid operations specifications
            $operations = array(
                'do'	=> array('kind' => 'ACTION_HANDLER','dir' => 'actions'),    // do something server-side
                'get'	=> array('kind' => 'DATA_PROVIDER',	'dir' => 'data'),       // return some data 
                'show'	=> array('kind' => 'APPLICATION',	'dir' => 'apps')        // output rendering information (UI)
            );               
            $container = Container::getInstance();
            
            if(!$root) {
                $context_orig = $container->get('context');
                // stack original container register
                $register = $container->register();
                // stack original context (request and response sub-objects are cloned as well)
                $context = clone $context_orig;
                $container->set('context', $context);
            }
            else {
                $context = $container->get('context');
            }
            
            $getOperationOutput = function($script) use($context) {
                ob_start();	                
                try {
                    include($script); 
                }
                catch(\Exception $e) {
                    // an exception with code 0 is an explicit process halt with no errror
                    if($e->getCode() != 0) {
                        // retrieve current HTTP response
                        $response = $context->httpResponse();
                        // build response with error details
                        $response
                        // set status and body according to raised exception
                        ->status(qn_error_http($e->getCode()))
                        ->header('Content-Type', 'application/json')
                        ->body( array_merge(
                                    (array) $response->body(), 
                                    [ 'errors' => [ qn_error_name($e->getCode()) => $e->getMessage() ] ]
                                )
                        )
                        // send HTTP response
                        ->send();
                    }
                }
                return ob_get_clean();
            };

            $request = $context->httpRequest();
            $request->body($body);
        
            $operation = explode(':', $operation);
            if(count($operation) > 1) {
                $visibility = array_shift($operation);
                if($visibility == 'private') $resolved['visibility'] = $visibility;
            }
            $resolved['operation'] = $operation[0];
            $parts = explode('_', $resolved['operation']);
            if(count($parts) > 0) {
                // use first part as package name
                $resolved['package'] = array_shift($parts);
                // use reamining parts to build script path
                if(count($parts) > 0) {
                    $resolved['script'] = implode('/', $parts).'.php';     
                }
            }
            

            // if package has a custom configuration file, load it
            if(!is_null($resolved['package']) && is_file('packages/'.$resolved['package'].'/config.inc.php')) {	
                include('packages/'.$resolved['package'].'/config.inc.php');
            }
            // if no request is specified, if possible set DEFAULT_PACKAGE/DEFAULT_APP as requested script
            if(is_null($resolved['type'])) {
                if(is_null($resolved['package'])) {
                    // send 404 HTTP response
                    throw new Exception("no default package", QN_ERROR_UNKNOWN_OBJECT);
                }
                if(defined('DEFAULT_APP')) {
                    $resolved['type'] = 'show';
                    $resolved['script'] = config('DEFAULT_APP').'.php';
                    // maintain current URI consistency
                    $request->uri()->set('show', $resolved['package'].'_'.config('DEFAULT_APP'));
                }
                else {
                    throw new \Exception("No default app for package {$resolved['package']}", QN_ERROR_UNKNOWN_OBJECT);        
                }
            }

            // include resolved script, if any
            if(isset($operations[$resolved['type']])) {
                $operation_conf = $operations[$resolved['type']];
                // normalize: remove operation parameter from request body, if any
                if($request->get($resolved['type']) == $resolved['operation']) {
                    $request->del($resolved['type']);
                }
                // store current operation into context
                $context->set('operation', $resolved['operation']);
                // if no app is specified, use the default app (if any)
                if(empty($resolved['script']) && defined('DEFAULT_APP')) $resolved['script'] = config('DEFAULT_APP').'.php';
                $filename = 'packages/'.$resolved['package'].'/'.$operation_conf['dir'].'/'.$resolved['script'];
                // set current dir according to visibility (i.e. 'public' or 'private')
                chdir(QN_BASE_DIR.'/'.$resolved['visibility']);
                if(!is_file($filename)) {
                    throw new \Exception("Unknown {$operation_conf['kind']} {$resolved['visibility']}:{$resolved['operation']}", QN_ERROR_UNKNOWN_OBJECT);        
                }
                // export as constants all parameters declared with config\define() to make them accessible through global scope
                export_config();
             
                if(!$root) {
                    // include and execute requested script
                    $result = $getOperationOutput($filename);
                }
                else {
                    include($filename); 
                }
            }
            
            // restore context
            if(!$root) {
                // restore original context
                $container->set('context', $context_orig);
                // restore container register
                $container->register($register);
            }

            return $result;
        }

        
		/**
		* Loads a class file from its class name (compatible with Zend classes)
		*
		* @static
		* @example	load_class('db/DBConnection');
		* @param	string	$class_name	in case the actual name of the class differs from the class file name (which may be the case when using namespaces)
		* @return	bool
		*/
		public static function load_class($class_name) {
			$result = false;
            
            if(class_exists($class_name, false) || isset($GLOBALS['QNlib_loading_classes'][$class_name])) {
                $result = true;
            }
            else {
                $GLOBALS['QNlib_loading_classes'][$class_name] = true;
                $file_path = QN_BASE_DIR.'/lib/'.str_replace('\\', '/', $class_name);
                // use Qinoa class extention
                if(file_exists($file_path.'.class.php')) $result = include_once $file_path.'.class.php';
                // Fallback to simple php extension
                else if(file_exists($file_path.'.php')) $result = include_once $file_path.'.php';
                else {
                    // give up an relay to next registered loader, if any
                }
                unset($GLOBALS['QNlib_loading_classes'][$class_name]);
            }
			return $result;
		}       

	}

	/**
	* We add some standalone functions to relieve the user from the scope resolution notation.
	*/
	function get_url($server_port=true, $query_string=true) {
		return QNlib::get_url($server_port, $query_string);
	}
	
	//Initialize the QNlib class for further 'load_class' calls
	QNlib::init();
}
namespace {
    // provide an "eQual" class to global scope
    class eQual extends config\QNlib {}
}