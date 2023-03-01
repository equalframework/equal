<?php
/**
*    This file is part of the eQual framework.
*    https://github.com/cedricfrancoys/equal
*
*    Some Rights Reserved, Cedric Francoys, 2010-2021
*    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Lesser General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace {
    define('__EQ_LIB', true) or die('fatal error: __EQ_LIB already defined or cannot be defined');

    /**
     *    All constants required by the core are prefixed with QN_
     *    (in addition, user might define its own constants following his own formatting rules)
     */

    /**
     * Current version of eQual
     */
    define('QN_VERSION', '1.0.0');

    /**
     * Root directory of current install
     */
    define('QN_BASEDIR', realpath(dirname(__FILE__)));

    /**
     * Error codes
     * we use negative values to make it possible to distinguish error codes from object ids
     */
    define('QN_ERROR_UNKNOWN',             -1);        // something went wrong (check the logs)
    define('QN_ERROR_MISSING_PARAM',       -2);        // one or more mandatory parameters are missing
    define('QN_ERROR_INVALID_PARAM',       -4);        // one or more parameters have invalid or incompatible value
    define('QN_ERROR_SQL',                 -8);        // error while building SQL query or processing it (check that object class matches DB schema)
    define('QN_ERROR_UNKNOWN_OBJECT',     -16);        // unknown resource (class, object, view, ...)
    define('QN_ERROR_NOT_ALLOWED',        -32);        // action violates some rule (including UPLOAD_MAX_FILE_SIZE for binary fields) or user don't have required permissions
    define('QN_ERROR_LOCKED_OBJECT',      -64);        // object is currently locked by another process
    define('QN_ERROR_CONFLICT_OBJECT',   -128);        // version conflict
    define('QN_ERROR_INVALID_USER',      -256);        // auth failure
    define('QN_ERROR_UNKNOWN_SERVICE',   -512);        // server error : missing service
    define('QN_ERROR_INVALID_CONFIG',   -1024);        // server error : faulty configuration



    /**
     * Debugging modes and levels
     */
    // debugging modes
    define('QN_MODE_PHP',          1);
    define('QN_MODE_SQL',          2);
    define('QN_MODE_ORM',          4);
    define('QN_MODE_API',          8);
    define('QN_MODE_APP',          16);

    // debugging levels
    define('QN_REPORT_DEBUG',       E_USER_DEPRECATED);     // 16384
    define('QN_REPORT_INFO',        E_USER_NOTICE);         // 1024
    define('QN_REPORT_WARNING',     E_USER_WARNING);        // 512
    define('QN_REPORT_ERROR',       E_USER_ERROR);          // 256
    define('QN_REPORT_FATAL',       E_ERROR);               // 1


    /**
     * Logs storage directory
     *
     * Note: ensure http service has read/write permissions on this directory
     */
    define('QN_LOG_STORAGE_DIR', QN_BASEDIR.'/log');

    // EventHandler will deal with error and debug messages depending on debug source value
    ini_set('html_errors', false);                              // prevent HTML in logs
    ini_set('error_log', QN_LOG_STORAGE_DIR.'/error.log');      // force PHP logs output to specific file


    /**
     * Users & Groups permissions masks
     */
    define('QN_R_CREATE',    1);
    define('QN_R_READ',      2);
    define('QN_R_WRITE',     4);
    define('QN_R_DELETE',    8);
    define('QN_R_MANAGE',   16);

    /**
     * Built-in Users and Groups
     *
     * Note : make sure that the ids in DB are set and matching these
     */
    define('QN_GUEST_USER_ID',       0);
    define('QN_ROOT_USER_ID',        1);

    // default group (all users are members of the default group)
    define('QN_ROOT_GROUP_ID',       1);
    define('QN_DEFAULT_GROUP_ID',    2);


    /*
        Error-utility functions (global namespace)
    */

    /**
     * Mapper from internal error codes to string constants
     */
    function qn_error_name($error_id) {
        switch($error_id) {
            case QN_ERROR_MISSING_PARAM:    return 'MISSING_PARAM';
            case QN_ERROR_INVALID_PARAM:    return 'INVALID_PARAM';
            case QN_ERROR_SQL:              return 'SQL_ERROR';
            case QN_ERROR_NOT_ALLOWED:      return 'NOT_ALLOWED';
            case QN_ERROR_UNKNOWN_OBJECT:   return 'UNKNOWN_OBJECT';
            case QN_ERROR_INVALID_CONFIG:   return 'INVALID_CONFIG';
            case QN_ERROR_UNKNOWN_SERVICE:  return 'UNKNOWN_SERVICE';
            case QN_ERROR_LOCKED_OBJECT:    return 'LOCKED_OBJECT';
            case QN_ERROR_CONFLICT_OBJECT:  return 'CONFLICT_OBJECT';
            case QN_ERROR_INVALID_USER:     return 'INVALID_CREDENTIALS';
        }
        return 'UNKNOWN_ERROR';
    }

    /**
     * Mapper from string constants to internal error codes
     */
    function qn_error_code($error_name) {
        switch($error_name) {
            case 'MISSING_PARAM':       return QN_ERROR_MISSING_PARAM;
            case 'INVALID_PARAM':       return QN_ERROR_INVALID_PARAM;
            case 'SQL_ERROR':           return QN_ERROR_SQL;
            case 'NOT_ALLOWED':         return QN_ERROR_NOT_ALLOWED;
            case 'UNKNOWN_OBJECT':      return QN_ERROR_UNKNOWN_OBJECT;
            case 'INVALID_CONFIG':      return QN_ERROR_INVALID_CONFIG;
            case 'UNKNOWN_SERVICE':     return QN_ERROR_UNKNOWN_SERVICE;
            case 'LOCKED_OBJECT':       return QN_ERROR_LOCKED_OBJECT;
            case 'CONFLICT_OBJECT':     return QN_ERROR_CONFLICT_OBJECT;
            case 'INVALID_CREDENTIALS': return QN_ERROR_INVALID_USER;
        }
        return QN_ERROR_UNKNOWN;
    }

    /**
     * Mapper from internal error codes to HTTP codes
     */
    function qn_error_http($error_id) {
        switch($error_id) {
            case 0:                         return 200;
            case QN_ERROR_MISSING_PARAM:    return 400;     // 'Bad Request'            missing data or invalid format for mandatory parameter
            case QN_ERROR_INVALID_PARAM:    return 400;
            case QN_ERROR_SQL:              return 456;     // 'Unrecoverable Error'    an unhandled scenario append and operation could not be performed
            case QN_ERROR_NOT_ALLOWED:      return 403;     // 'Forbidden'              user has not enough privilege to perform requested operation (includes method not allowed-
            case QN_ERROR_UNKNOWN_OBJECT:   return 404;     // 'Not Found'              object or route does not exist
            case QN_ERROR_LOCKED_OBJECT:    return 423;     // 'Locked'                 resource is currently locked
            case QN_ERROR_CONFLICT_OBJECT:  return 409;     // 'Conflict'               version conflict or broken 'unique' rule
            case QN_ERROR_INVALID_USER:     return 401;     // 'Unauthorized'           auth required or auth failure
            // server errors
            case QN_ERROR_UNKNOWN:
            case QN_ERROR_INVALID_CONFIG:   return 500;
            case QN_ERROR_UNKNOWN_SERVICE:  return 503;
        }
        // fallback to 'Internal Server Error': something went wrong (details should be available in the log)
        return 500;
    }

    function qn_debug_code_name($code) {
        switch($code) {
            case QN_REPORT_DEBUG:    return 'DEBUG';
            case QN_REPORT_INFO:     return 'INFO';
            case QN_REPORT_WARNING:  return 'WARNING';
            case QN_REPORT_ERROR:    return 'ERROR';
            case QN_REPORT_FATAL:    return 'FATAL';
        }
        return 'UNKNOWN';
    }

    function qn_debug_mode_name($mode) {
        switch($mode) {
            case QN_MODE_PHP:    return 'PHP';
            case QN_MODE_SQL:    return 'SQL';
            case QN_MODE_ORM:    return 'ORM';
            case QN_MODE_API:    return 'API';
            case QN_MODE_APP:    return 'APP';
        }
        return 'UNKNOWN';
    }

    /**
     * Add global system-related constants
     * (which cannot be modified by other scripts)
     */
    $constants_schema = [];
    if(!file_exists(QN_BASEDIR.'/config/schema.json')) {
        die('Missing mandatory config schema.');
    }
    else {
        $data = file_get_contents(QN_BASEDIR.'/config/schema.json');
        if(!($constants_schema = json_decode($data, true))) {
            die('Invalid config schema.');
        }

        // pass-1 - process properties defined in config file
        if(file_exists(QN_BASEDIR.'/config/config.json')) {
            $data = file_get_contents(QN_BASEDIR.'/config/config.json');
            if(($config = json_decode($data, true))) {
                foreach($config as $property => $value) {
                    config\define($property, $value);
                }
            }
            else {
                die('Invalid config file.');
            }
        }
        // pass-2 - process instant properties not present in config
        foreach($constants_schema as $property => $descriptor) {
            if(isset($descriptor['instant']) && $descriptor['instant']) {
                if(!config\defined($property)) {
                    $value = null;
                    if(isset($descriptor['environment'])) {
                        if(($env = getenv($descriptor['environment'])) !== false) {
                            $value = $env;
                        }
                    }
                    if(is_null($value) && isset($descriptor['default'])) {
                        $value = $descriptor['default'];
                    }
                    config\define($property, $value);
                }
                config\export($property);
            }
            elseif(!config\defined($property) && isset($descriptor['default'])) {
                config\define($property, $descriptor['default']);
            }
        }
    }
    // #todo - deprecate
    // support for legacy config
    if(file_exists(QN_BASEDIR.'/config/config.inc.php')) {
        include_once(QN_BASEDIR.'/config/config.inc.php');
    }
    else {
        if((@include_once(QN_BASEDIR.'/config/default.inc.php')) === false) {
            // die('oops, default root config file is missing');
        }
    }

/*
// #DEBUG
// for debugging, uncomment this to force the header of generated HTTP response
        header("HTTP/1.1 200 OK");
        header('Content-type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD,TRACE');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Expose-Headers: *');
        header('Allow: *');
        flush();
*/
}
namespace config {
    use equal\services\Container;
    use equal\orm\Field;

    /*
     * This section adds some config-utility functions to the 'config' namespace.
     */


    function encrypt($string) {
        $output = false;
        if(\defined('CIPHER_KEY')) {
            $cipher_algo = "AES-256-CBC";
            $secret_key = \constant('CIPHER_KEY');
            $key = hash('sha256', $secret_key);
            $iv = substr(implode('', range(0, 12)), 0, openssl_cipher_iv_length($cipher_algo));
            $output = openssl_encrypt($string, $cipher_algo, $key, 0, $iv);
            $output = base64_encode($output);
        }
        return $output;
    }

    function decrypt($string) {
        $output = false;
        if(\defined('CIPHER_KEY')) {
            $cipher_algo = "AES-256-CBC";
            $secret_key = \constant('CIPHER_KEY');
            $key = hash('sha256', $secret_key);
            $iv = substr(implode('', range(0, 12)), 0, openssl_cipher_iv_length($cipher_algo));
            $output = openssl_decrypt(base64_decode($string), $cipher_algo, $key, 0, $iv);
        }
        return $output;
    }

    function strtoint($value) {
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


    /**
     * Adds a parameter to the configuration array
     */
    function define($name, $value) {
        if(!isset($GLOBALS['QN_CONFIG_ARRAY'])) {
            $GLOBALS['QN_CONFIG_ARRAY'] = [];
        }
        $GLOBALS['QN_CONFIG_ARRAY'][$name] = $value;
    }

    /**
     * Checks if a parameter has already been defined
     */
    function defined($name) {
        return \defined($name) || isset($GLOBALS['QN_CONFIG_ARRAY'][$name]);
    }

    /**
     * Retrieve a configuration parameter as a constant.
     */
    function constant($name, $default=null) {
        return (isset($GLOBALS['QN_CONFIG_ARRAY'][$name]))?$GLOBALS['QN_CONFIG_ARRAY'][$name]:$default;
    }

    /**
     * Register a service by assigning an identifier (name) to a class (stored under `/lib`).
     *
     * This method can be invoked in local config files to register a custom service and/or to override any existing service,
     * and uses the `QN_SERVICES_POOL` global array which is used by the root container.
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
     * Retrieve  a configuration parameter.
     * @deprecated
     */
    function config($name, $default=null) {
        return (isset($GLOBALS['QN_CONFIG_ARRAY'][$name]))?$GLOBALS['QN_CONFIG_ARRAY'][$name]:$default;
    }


    /**
     * Exports a property as constant to the global scope.
     */
    function export($property) {
        global $constants_schema;
        // retrieve current value
        $value = constant($property);
        // handle shorthand notations
        if(isset($constants_schema[$property]) && $constants_schema[$property]['type'] == 'integer') {
            // handle binary masks on arbitrary values or pre-defined constants
            if(is_string($value) && (strpos($value, '|') !== false || strpos($value, '&') !== false)) {
                try {
                    $value = eval("return $value;");
                }
                catch(Exception $e) {
                    // ignore parse errors
                }
            }
            else {
                $value = strtoint($value);
            }
        }
        // handle encrypted values
        elseif(is_string($value) && substr($value, 0, 7) == 'cipher:') {
            $value = decrypt(substr($value, 7));
        }
        \define($property, $value);
    }

    /**
     * Export all parameters declared with `config\define()` function, as constants.
     *
     * After a call to this method, these params will be accessible in global scope.
     * @deprecated
     */
    function export_config() {
        if(isset($GLOBALS['QN_CONFIG_ARRAY'])) {
            foreach($GLOBALS['QN_CONFIG_ARRAY'] as $name => $value) {
                if(!\defined($name)) {
                    // handle crypted values
                    if(is_string($value) && substr($value, 0, 7) == 'cipher:') {
                        $value = decrypt(substr($value, 7));
                    }
                    \define($name, $value);
                }
                unset($GLOBALS['QN_CONFIG_ARRAY'][$name]);
            }
        }
        $GLOBALS['QN_CONFIG_EXPORTED'] = true;
    }

    /** @var \equal\php\Context */
    $last_context = null;

    class eQual {

        /**
         * Initialize eQual.
         *
         * Adds the library folder to the include path (library folder should contain the Zend framework if required).
         * This is the bootstrap method for setting everything in place.
         *
         * @static
         */
        public static function init() {
            chdir(QN_BASEDIR.'/');

            // enable inclusion and autoload of external classes
            if(file_exists(QN_BASEDIR.'/vendor/autoload.php')) {
                include_once(QN_BASEDIR.'/vendor/autoload.php');
            }

            // register own class loader
            spl_autoload_register(__NAMESPACE__.'\eQual::load_class');

            // check service container availability
            if(!is_callable('equal\services\Container::getInstance')) {
                throw new \Exception('eQual::init - Mandatory Container service is missing or cannot be instantiated.', QN_REPORT_FATAL);
            }
            // instantiate service container
            $container = Container::getInstance();

            // register names for common services and assign default classes
            // (these can be overridden in the `config.inc.php` of invoked package)
            $container->register([
                'report'    => 'equal\error\Reporter',
                'auth'      => 'equal\auth\AuthenticationManager',
                'access'    => 'equal\access\AccessController',
                'context'   => 'equal\php\Context',
                'validate'  => 'equal\data\DataValidator',
                'adapt'     => 'equal\data\adapt\DataAdapterProvider',
                'orm'       => 'equal\orm\ObjectManager',
                'route'     => 'equal\route\Router',
                'log'       => 'equal\log\Logger',
                'cron'      => 'equal\cron\Scheduler',
                'dispatch'  => 'equal\dispatch\Dispatcher'
            ]);

            // make sure mandatory dependencies are available (reporter requires context)
            try {
                $container->get(['report', 'context']);
            }
            catch(\Throwable $e) {
                // fallback to a manual HTTP 500
                header("HTTP/1.1 503 Service Unavailable");
                header('Content-type: application/json; charset=UTF-8');
                echo json_encode([
                        'errors' => ['UNKNOWN_SERVICE' => $e->getMessage()]
                    ],
                    JSON_PRETTY_PRINT);
                // and raise an exception (will be output in PHP error log)
                throw new \Exception("missing_mandatory_dependency", QN_REPORT_FATAL);
            }
            // register ORM classes autoloader
            try {
                $om = $container->get('orm');
                // init collections provider
                $container->get('equal\orm\Collections');
                spl_autoload_register([$om, 'getModel']);
            }
            catch(\Throwable $e) {
                throw new \Exception("autoload_register_failed", QN_REPORT_FATAL);
            }

        }

        public static function getLastContext() {
            global $last_context;
            return $last_context;
        }

        public static function inject(array $providers) {
            $result = [];
            // retrieve service container
            $container = Container::getInstance();

            // retrieve providers
            foreach($providers as $name) {
                $result[$name] = $container->get($name);
            }

            return $result;
        }

        /**
         * Announce the definition of an operation.
         *
         * Retrieve, adapt and validate expected parameters from the HTTP request and provide requested dependencies.
         * Also ensures that required parameters have been transmitted and sets default values for missing optional params.
         *
         * Accepted types for parameters types are PHP basic types: int, bool, float, string, array
         * Note: un-announced parameters in HTTP request are ignored (dropped)
         *
         * @static
         * @param    array      $announcement    Array holding the description of the script and its parameters.
         * @return   array      parameters and their final values
         * @throws   Exception  raises an exception if: a dependency failed to load, OR a mandatory param is missing OR a param has invalid value
         */
        public static function announce(array $announcement) {
            // 0) init vars
            $result = array();
            $mandatory_params = array();
            // retrieve service container
            $container = Container::getInstance();
            // retrieve required services
            /**
             * @var \equal\php\Context      $context
             * @var \equal\error\Reporter   $reporter
             */
            list($context, $reporter) = $container->get(['context', 'report']);
            // fetch body and method from HTTP request
            $request = $context->httpRequest();
            $body = (array) $request->body();
            $method = $request->method();
            $response = $context->httpResponse();

            $reporter->debug("method $method");

            // normalize $announcement array
            if(!isset($announcement['params'])) {
                $announcement['params'] = array();
            }

            // handle controller inheritance
            if(isset($announcement['extends']))  {
                // retrieve params from parent controller
                $op_descr = $context->get('operation');
                try {
                    $data = \eQual::run($op_descr['type'], $announcement['extends'], ['announce' => true], false);
                    if(!is_null($data) && isset($data['announcement']['params'])) {
                        $announcement['params'] = array_merge($data['announcement']['params'], $announcement['params']);
                    }
                }
                catch(\Exception $e) {
                    // ignore errors (non existing controller ?)
                }
            }

            // check response headers
            if(isset($announcement['response'])) {
                if(isset($announcement['response']['location']) && !isset($body['announce'])) {
                    header('Location: '.$announcement['response']['location']);
                    exit;
                }
                if(isset($announcement['response']['content-type'])) {
                    $response->headers()->setContentType($announcement['response']['content-type']);
                }
                if(isset($announcement['response']['content-disposition'])) {
                    $response->headers()->set('content-disposition', $announcement['response']['content-disposition']);
                }
                if(isset($announcement['response']['charset'])) {
                    $response->headers()->setCharset($announcement['response']['charset']);
                }

                if(isset($announcement['response']['accept-origin'])) {
                    // force param as an array
                    // elements of `accept-origin` are expected to be valid origins (@see https://tools.ietf.org/html/rfc6454#section-7)
                    $announcement['response']['accept-origin'] = (array) $announcement['response']['accept-origin'];
                    // retrieve origin from header
                    $request_origin = $request->header('origin');
                    // `Access-Control-Allow-Origin` must be a single URI (@see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin)
                    // so we check for a list of allowed URI
                    foreach($announcement['response']['accept-origin'] as $origin) {
                        // #todo use a compare method to handle explicit/implicit port notation
                        if(in_array($origin, ['*', $request_origin])) {
                            $response->header('Access-Control-Allow-Origin', $request_origin);
                            break;
                        }
                    }
                    // prevent requests from non-allowed origins (for non-https requests, this can be bypassed by manually setting requests header)
                    if($origin != '*' && $origin != $request_origin) {
                        // raise an exception with error details
                        throw new \Exception('origin_not_allowed', QN_ERROR_NOT_ALLOWED);
                    }
                    // set headers accordingly to response definition
                    // #todo allow to customize (override) these values
                    $response->header('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD,TRACE');
                    $response->header('Access-Control-Allow-Headers', '*');
                    // expose headers specific to eQual (#memo - mind case-match)
                    // (CORS defaults are: Cache-Control, Content-Language, Content-Length, Content-Type, Expires, Last-Modified, Pragma.)
                    $response->header('Access-Control-Expose-Headers', 'X-Total-Count');
                    $response->header('Access-Control-Allow-Credentials', 'true');
                }
                if(isset($announcement['response']['content-disposition'])) {
                    $response->header('Content-Disposition', $announcement['response']['content-disposition']);
                }

                // handle caching options, if any
                /*
                    Caching is only available for GET methods,
                    and offers support at URL level only (params in body are not considered).
                    Controllers can use cache-vary to tell which elements influence their resulting content.
                    Accepted values for cache-vary array : uri (default), user, origin, body.

                    // #todo export this part of the logic to a cache manager
                */
                if( $method == 'GET'
                    && isset($announcement['response']['cacheable'])
                    && $announcement['response']['cacheable']) {
                    // compute the cache ID
                    // remove 'cache' param from URI, if present
                    $request_id = trim(preg_replace('/&cache=[^&]*&/', '&', $request->uri().'&'), '&');
                    if(isset($announcement['response']['cache-vary'])) {
                        $vary = (array) $announcement['response']['cache-vary'];
                        if(in_array('user', $vary)) {
                            list($auth) = $container->get(['auth']);
                            $request_id .= '-'.$auth->userId();
                        }
                        if(in_array('origin', $vary)) {
                            $request_id .= '-'.$request->header('origin');
                        }
                        if(in_array('body', $vary)) {
                            $request_id .= '-'.$request->body();
                        }
                    }
                    $cache_id = md5($request_id);
                    // retrieve related filename
                    $cache_filename = QN_BASEDIR.'/cache/'.$cache_id;
                    // update context for further processing
                    $context->set('cache', true);
                    $context->set('cache-id', $cache_id);
                    // check if a validity expiration (in seconds) is defined for client-side
                    if(isset($announcement['response']['client-max-age'])) {
                        $context->set('client-max-age', intval($announcement['response']['client-max-age']));
                    }
                    // check if a validity expiration (in seconds) is defined for server-side
                    $serve_from_cache = true;
                    if(isset($announcement['response']['expires'])) {
                        $expires = intval($announcement['response']['expires']);
                        $age = time() - filemtime(realpath($cache_filename));
                        if($age >= $expires) {
                            $reporter->debug("expired cache-id {$cache_id}");
                            $serve_from_cache = false;
                        }
                    }
                    // handle manual request for invalidating the cache
                    if(isset($body['cache'])) {
                        if(in_array($body['cache'], [null, false, 0, '0'])) {
                            $reporter->debug("manual reset cache-id {$cache_id}");
                            $serve_from_cache = false;
                        }
                        // cache is a reserved parameter: no further process
                        unset($body['cache']);
                    }
                    // request is already present in cache
                    if(file_exists($cache_filename)) {
                        // cache was invalidated: remove related file
                        if(!$serve_from_cache) {
                            $reporter->debug("invalidating cache-id {$cache_id}");
                            unlink($cache_filename);
                        }
                        // cache is still valid: serve from cache
                        else {
                            // handle client cache expiry (no change)
                            if($request->header('If-None-Match') == $cache_id) {
                                // send "304 Not Modified"
                                $response
                                    ->status(304)
                                    ->send();
                                throw new \Exception('', 0);
                            }
                            $reporter->debug("serving from cache-id {$cache_id}");
                            list($headers, $result) = unserialize(file_get_contents($cache_filename));
                            // build response with cached headers
                            foreach($headers as $header => $value) {
                                $response->header($header, $value);
                            }
                            $response
                                // inject raw body
                                ->body($result, true)
                                // set status and body according to raised exception
                                ->status(200)
                                // send HTTP response
                                ->send();
                            exit();
                        }
                    }
                }
            }

            // check access restrictions
            if(isset($announcement['access']) && $method != 'OPTIONS') {
                list($access, $auth) = $container->get(['access', 'auth']);
                if(isset($announcement['access']['visibility'])) {
                    if($announcement['access']['visibility'] == 'private' && php_sapi_name() != 'cli') {
                        throw new \Exception('restricted_operation', QN_ERROR_NOT_ALLOWED);
                    }
                    if($announcement['access']['visibility'] == 'protected')  {
                        // #memo - regular rules will apply (non identified user shouldn't be granted unless DEFAULT_RIGHTS allow it)
                        // throw new \Exception('restricted_operation', QN_ERROR_NOT_ALLOWED);
                    }
                }
                if(isset($announcement['access']['users'])) {
                    // disjunctions on users
                    $current_user_id = $auth->userId();
                    if($current_user_id != QN_ROOT_USER_ID) {
                        // #todo - add support for checks on login
                        $allowed = false;
                        $users = (array) $announcement['access']['users'];
                        foreach($users as $user_id) {
                            if($user_id == $current_user_id) {
                                $allowed = true;
                                break;
                            }
                        }
                        if(!$allowed) {
                            throw new \Exception('restricted_operation', QN_ERROR_NOT_ALLOWED);
                        }
                    }
                }
                if(isset($announcement['access']['groups'])) {
                    $current_user_id = $auth->userId();
                    if($current_user_id != QN_ROOT_USER_ID) {
                        // disjunctions on groups
                        $allowed = false;
                        $groups = (array) $announcement['access']['groups'];
                        foreach($groups as $group) {
                            if($access->hasGroup($group)) {
                                $allowed = true;
                                break;
                            }
                        }
                        if(!$allowed) {
                            throw new \Exception('restricted_operation', QN_ERROR_NOT_ALLOWED);
                        }
                    }
                }
            }


            // 1) check if all required parameters have been received

            // build mandatory fields array
            foreach($announcement['params'] as $param => $config) {
                if(isset($config['required']) && $config['required']) {
                    $mandatory_params[] = $param;
                }
            }
            // if at least one mandatory param is missing
            $missing_params = array_values(array_diff($mandatory_params, array_keys($body)));
            if( count($missing_params)
                || isset($body['announce'])
                || $method == 'OPTIONS' ) {
                // no feedback about services
                if(isset($announcement['providers'])) {
                    unset($announcement['providers']);
                }
                // no feedback about constants
                if(isset($announcement['constants'])) {
                    unset($announcement['constants']);
                }
                // add announcement to response body
                $response->body(['announcement' => $announcement]);
                if(isset($body['announce']) || $method == 'OPTIONS') {
                    // user asked for the announcement or browser requested fingerprint
                    $response->status(200)
                        // allow browser to cache the response for 1 year
                        ->header('Cache-Control', 'max-age=31536000')
                        // default content type and disposition
                        ->header('Content-Type', 'application/json')
                        ->header('Content-Disposition', 'inline')
                        // mandatory headers for CORS validation (OPTIONS request must have Content-Type explicitly set)
                        ->header('Access-Control-Allow-Headers', 'Access-Control-Request-Method, Access-Control-Request-Headers, Origin, Content-Type, Accept, X-Requested-With, Referrer-Policy, Referer, Cookie')
                        //->header('Access-Control-Allow-Headers', '*')
                        ->send();
                    throw new \Exception('', 0);
                }
                // raise an exception with error details
                throw new \Exception(implode(',', $missing_params), QN_ERROR_MISSING_PARAM);
            }

            // 2) find any missing parameters

            $allowed_params = array_keys($announcement['params']);
            $unknown_params = array_diff(array_keys($body), $allowed_params);
            foreach($unknown_params as $unknown_param) {
                $reporter->debug("dropped unexpected parameter '{$unknown_param}'");
                unset($body[$unknown_param]);
            }
            $missing_params = array_diff($allowed_params, array_intersect($allowed_params, array_keys($body)));

            // 3) build result array and set default values for optional parameters that are missing and for which a default value is defined

            $invalid_params = [];
            /** @var \equal\data\adapt\DataAdapterProvider */
            $dap = $container->get('adapt');
            // #todo - we should use the adapter based on content-type header, if any
            /** @var \equal\data\adapt\DataAdapter */
            $adapter = $dap->get('json');
            foreach($announcement['params'] as $param => $config) {
                // #memo - at some point condition had a clause "|| empty($body[$param])", remember not to alter received data!
                if(in_array($param, $missing_params) && isset($config['default'])) {
                    $body[$param] = $config['default'];
                }
                if(!array_key_exists($param, $body)) {
                    // ignore optional params without default value (this allows PATCH of objects on specific fields only)
                }
                else {
                    $f = new Field($config);
                    $result[$param] = $adapter->adaptIn($body[$param], $f->getUsage());
                    // #todo - check value validity according to Usage
                    /*
                    // convert value from input format + validate type and usage constraints
                    try {
                        $f->validate();
                        $result[$param] = $f->get();
                    }
                    catch(\Exception $e) {
                        // only mandatory params raise an exception
                        if(in_array($param, $mandatory_params)) {
                            $error = @unserialize($e->getMessage()));
                            throw new \Exception(serialize([$param => $error]), QN_ERROR_INVALID_PARAM);
                        }
                        else {
                            if(isset($config['default'])) {
                                $reporter->warning("invalid value for non-mandatory parameter '{$param}' reverted to default '{$config['default']}'");
                                $result[$param] = $config['default'];
                            }
                            else {
                                $reporter->warning("dropped invalid non-mandatory parameter '{$param}'");
                            }
                        }
                    }
                    */
                }
            }

            // 4) validate values types and handle optional attributes
            // #transition - to remove
            $validator = $container->get('validate');
            foreach($result as $param => $value) {
                $config = $announcement['params'][$param];
                // build constraints array
                $constraints = [];
                // adapt type to match PHP internals
                $constraints[] = ['kind' => 'type', 'rule' => $config['type']];
                // append explicit constraints
                foreach(array('min', 'max', 'in', 'not in', 'pattern', 'selection') as $constraint) {
                    if(isset($config[$constraint])) {
                        $constraints[] = ['kind' => $constraint, 'rule' => $config[$constraint]];
                    }
                }

                // validate parameter's value
                if(!$validator->validate($value, $constraints)) {
                    if(!in_array($param, $mandatory_params)) {
                        // if it has a default value, assign to it
                        if(isset($config['default'])) {
                            $reporter->warning("invalid value {$value} for non-mandatory parameter '{$param}' reverted to default '{$config['default']}'");
                            $result[$param] = $config['default'];
                        }
                        else {
                            // otherwise, drop it
                            $reporter->warning("dropped invalid non-mandatory parameter '{$param}'");
                            unset($result[$param]);
                        }
                    }
                    else $invalid_params[$param] = 'broken_constraint';
                }
            }

            // report received parameters
            $reporter->debug("params: ".json_encode($result));

            if(count($invalid_params)) {
                // no feedback about services
                if(isset($announcement['providers'])) unset($announcement['providers']);
                // no feedback about constants
                if(isset($announcement['constants'])) unset($announcement['constants']);
                // add announcement to response body
                $response->body(['announcement' => $announcement]);
                foreach($invalid_params as $invalid_param => $error_id) {
                    // raise an exception with error details
                    throw new \Exception(serialize([$invalid_param => [$error_id => 'Invalid parameter']]), QN_ERROR_INVALID_PARAM);
                }
            }

            // 5) check for requested providers

            if(isset($announcement['providers']) && count($announcement['providers'])) {
                $providers = [];
                // inject dependencies
                foreach($announcement['providers'] as $key => $name) {
                    // handle custom name mapping
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
                    if(defined($name) && !\defined($name)) {
                        $value = constant($name);
                        export($name, $value);
                    }
                    if(!\defined($name)) {
                        throw new \Exception("Requested constant {$name} is missing from configuration", QN_ERROR_INVALID_CONFIG);
                    }
                }
            }

            return $result;
        }


        /**
         * Execute a given operation.
         *
         * This method stacks the context, sets URI and body according to arguments, and calls the targeted script.
         * In case the operation is not defined, a QN_ERROR_UNKNOWN_OBJECT error is raised (HTTP 404)
         *
         * @param string    $type           Type of operation to run ('do', 'get', 'show')
         * @param string    $operation      Path of the operation to run (e.g. 'core_model_collect')
         * @param array     $body           Payload to relay to the controller (associative array mapping params with their values).
         * @param boolean   $root           Flag to run the operation as a first (root) call (following calls are stacked and their output is buffered).
         *
         * @example run('get', 'model_read', ['entity' => 'core\Group', 'id'=> 1]);
         */
        public static function run($type, $operation, $body=[], $root=false) {
            trigger_error("PHP::calling run method for $type:$operation", QN_REPORT_DEBUG);
            global $last_context;

            $result = '';
            $resolved = [
                'type'       => $type,       // 'do', 'get' or 'show'
                'operation'  => null,        // {package}_{script-path}
                'visibility' => 'public',    // 'public', 'protected', or 'private'
                'package'    => null,        // {package}
                'script'     => null         // {path/to/script.php}
            ];
            // define valid operations specifications
            $operations = [
                'do'    => ['kind' => 'ACTION_HANDLER', 'dir' => 'actions'],    // do something server-side
                'get'   => ['kind' => 'DATA_PROVIDER',  'dir' => 'data'   ],    // return some data
                'show'  => ['kind' => 'APPLICATION',    'dir' => 'apps'   ]     // output rendering information (UI)
            ];
            $container = Container::getInstance();

            if(!$root) {
                $context_orig = $container->get('context');
                // stack original container register
                $register = $container->register();
                // stack original context (request and response sub-objects are cloned as well)
                /** @var \equal\php\Context */
                $context = clone $context_orig;
                // make new context service as the one to be returned when requested by global services
                $container->set('context', $context);
            }
            else {
                /** @var \equal\php\Context */
                $context = $container->get('context');
            }

            /** @var \equal\error\Reporter */
            $reporter = $container->get('report');

            $getOperationOutput = function($script) use($context) {
                ob_start();
                try {
                    include($script);
                }
                catch(\Throwable $e) {
                    $error_code = $e->getCode();
                    if($e instanceof \Error) {
                        $error_code = QN_ERROR_UNKNOWN;
                    }
                    // an exception with code 0 is an explicit process halt with no error
                    if($error_code != 0) {
                        $msg = $e->getMessage();
                        // handle serialized objects as message
                        $data = @unserialize($msg);
                        if ($data !== false) {
                            $msg = $data;
                        }
                        // retrieve current HTTP response
                        $response = $context->httpResponse();
                        // build response with error details
                        $response
                        // set status and body according to raised exception
                        ->status(qn_error_http($error_code))
                        ->header('Content-Type', 'application/json')
                        ->extendBody( [ 'errors' => [ qn_error_name($error_code) => $msg ] ] )
                        // send HTTP response
                        ->send();
                    }
                }
                return ob_get_clean();
            };

            /** @var \equal\http\HttpRequest */
            $request = $context->httpRequest();
            $request->body($body);

            $operation = explode(':', $operation);
            if(count($operation) > 1) {
                $visibility = array_shift($operation);
                if($visibility == 'private') {
                    $resolved['visibility'] = $visibility;
                }
            }
            $resolved['operation'] = $operation[0];
            $parts = explode('_', $resolved['operation']);
            if(count($parts) > 0) {
                // use first part as package name
                $resolved['package'] = array_shift($parts);
                // use remaining parts to build script path
                if(count($parts) > 0) {
                    $resolved['script'] = implode('/', $parts).'.php';
                }
            }

            $reporter->debug(json_encode([
                    'uri'       => (string) $request->getUri(),
                    'headers'   => $request->getHeaders(true),
                    'body'      => $request->getBody()
                ], JSON_PRETTY_PRINT));

            // load package custom configuration, if any
            if(!is_null($resolved['package']) && is_file(QN_BASEDIR.'/packages/'.$resolved['package'].'/config.inc.php')) {
                include(QN_BASEDIR.'/packages/'.$resolved['package'].'/config.inc.php');
            }
            // if no request is specified, if possible set DEFAULT_PACKAGE/DEFAULT_APP as requested script
            if(is_null($resolved['type'])) {
                if(is_null($resolved['package'])) {
                    // send 404 HTTP response
                    throw new \Exception("no_default_package", QN_ERROR_UNKNOWN_OBJECT);
                }
                if(defined('DEFAULT_APP')) {
                    $resolved['type'] = 'show';
                    $resolved['script'] = constant('DEFAULT_APP').'.php';
                    // maintain current URI consistency
                    $request->uri()->set('show', $resolved['package'].'_'.constant('DEFAULT_APP'));
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
                $context->set('operation', $resolved);
                // if no app is specified, use the default app (if any)
                if(empty($resolved['script']) && defined('DEFAULT_APP')) {
                    $resolved['script'] = constant('DEFAULT_APP').'.php';
                }
                $filename = QN_BASEDIR.'/packages/'.$resolved['package'].'/'.$operation_conf['dir'].'/'.$resolved['script'];
                if(!is_file($filename)) {
                    // always try to fallback to core package (for short syntax calls)
                    $filename = QN_BASEDIR.'/packages/core/'.$operation_conf['dir'].'/'.$resolved['package'].'/'.$resolved['script'];
                    if(!is_file($filename)) {
                        $filename = QN_BASEDIR.'/packages/core/'.$operation_conf['dir'].'/'.$resolved['package'].'.php';
                        if(!is_file($filename)) {
                            throw new \Exception("Unknown {$operation_conf['kind']} ({$resolved['type']}) {$resolved['visibility']}:{$resolved['operation']}", QN_ERROR_UNKNOWN_OBJECT);
                        }
                    }
                }

                // force timezone to UTC
                // #memo - this is to avoid being impacted by daylight saving offset
                // #memo - we expect the DBMS to store dates in UTC as well
                date_default_timezone_set('UTC');

                if(!$root) {
                    // include and execute requested script
                    $result = $getOperationOutput($filename);
                }
                else {
                    // handle output buffering
                    ob_start();
                    include($filename);
                    $result = ob_get_clean();
                    // cache output result, if requested (@see announce() method)
                    if($context->get('cache')) {
                        $cache_id = $context->get('cache-id');
                        if( ($max_age = $context->get('client-max-age')) ) {
                            $context->httpResponse()->header('Cache-Control', "max-age={$max_age}");
                        }
                        $context->httpResponse()->header('Etag', $cache_id);
                        $headers = $context->httpResponse()->headers()->toArray();
                        file_put_contents(QN_BASEDIR.'/cache/'.$cache_id, serialize([$headers, $result]));
                        $reporter->debug("stored cache-id {$cache_id}");
                    }
                }
                trigger_error("PHP::result: $result", QN_REPORT_DEBUG);
            }

            // restore context
            if(!$root) {
                $last_context = $context;
                // restore original context
                if(isset($context_orig)) {
                    $container->set('context', $context_orig);
                }
                // restore container register
                if(isset($register)) {
                    $container->register($register);
                }
            }

            return $result;
        }


        /**
         * Load a given class.
         *
         * @static
         * @param   string    $class_name    in case the actual name of the class differs from the class file name (which may be the case when using namespaces)
         * @return  bool
         *
         * @example load_class('equal\db\DBConnection');
         */
        public static function load_class($class_name) {
            $result = false;
            if(class_exists($class_name, false) || isset($GLOBALS['eQual_loading_classes'][$class_name])) {
                // class is already loaded or being loaded
                $result = true;
            }
            else {
                // mark class as being loaded
                $GLOBALS['eQual_loading_classes'][$class_name] = true;
                $file_path = QN_BASEDIR.'/lib/'.str_replace('\\', '/', $class_name);
                // use 'class.php' extension
                if(file_exists($file_path.'.class.php')) {
                    $result = include_once $file_path.'.class.php';
                }
                // Fallback to simple php extension
                else if(file_exists($file_path.'.php')) {
                    $result = include_once $file_path.'.php';
                }
                else {
                    // give up an relay to next registered loader, if any
                }
                // set class as fully loaded
                unset($GLOBALS['eQual_loading_classes'][$class_name]);
            }
            return $result;
        }

    }

    // Initialize the eQual class for further 'load_class' calls
    eQual::init();
}
namespace {

    /**
     * inject standalone functions into global scope (to allow using methods without scope resolution notation)
     */
    function run(string $type, string $operation, array $body=[], $root=false) {
        return config\eQual::run($type, $operation, $body, $root);
    }

    function announce(array $announcement) {
        return config\eQual::announce($announcement);
    }

    /*
    function inject(array $providers) {
        return config\eQual::inject($providers);
    }
    */

    class eQual {
        // expose static methods
        /*
        public static function __callStatic($name, $arguments) {
            return call_user_func_array('config\eQual::'.$name, $arguments);
        }
        */

        /**
         * Call run, then returns it result or raises/relay an Exception if an error occurs.
         * (So it can be used with the global exceptions logic or with local try/catch blocks.)
         *
         * @param string    $type           Type of operation to run ('do', 'get', 'show')
         * @param string    $operation      Path of the operation to run (e.g. 'core_model_collect')
         * @param array     $body           Payload to relay to the controller (associative array).
         * @param boolean   $root           Flag to run the operation as a first (root) call (following calls are stacked).
         *
         * @return  array        Associative array holding the result of the call.
         * @throws  Exception    In cas of error, an exception is raised relaying the error code and the message of the error.
         */
        public static function run($type, $operation, $body=[], $root=false) {
            $result = config\eQual::run($type, $operation, $body, $root);
            $data = json_decode($result, true);
            // if result is not JSON, return raw data
            if(is_null($data)) {
                return $result;
            }
            if($data && isset($data['errors'])) {
                // raise an exception with first returned error code
                foreach($data['errors'] as $name => $message) {
                    if(is_array($message)) {
                        $message = serialize($message);
                    }
                    throw new \Exception($message, qn_error_code($name));
                }
            }
            return $data;
        }

        public static function announce(array $announcement) {
            return config\eQual::announce($announcement);
        }

        public static function inject(array $providers) {
            return config\eQual::inject($providers);
        }

        public static function get_last_context() {
            return config\eQual::getLastContext();
        }
    }

}
