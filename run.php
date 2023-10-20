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
use equal\php\Context;
use equal\route\Router;
/**
*
*  This is the root entry point and acts as dispatcher.
*  Its role is to set up the context and handle the client request.
*  Dispatching consists of resolving targeted operation and include related script file.
*
*
*  Usage examples:
*
*    CLI
*            equal.run --get=test_hello
*    HTTP
*            /?get=resiway_tests&id=1&test=2
*    PHP
*            run('get', 'utils_sql-schema', ['package'=>'core']);
*
*/

/*
 Load bootstrap library: system constants and functions definitions
 (eQual library allows to include required files and classes)
*/
$bootstrap = dirname(__FILE__).'/eq.lib.php';
if( (include($bootstrap)) === false ) {
    die('eQual lib is missing');
}

try {
    // 1) retrieve current HTTP context

    // get PHP context
    $context = Context::getInstance();
    // fetch current HTTP request from context
    $request = $context->getHttpRequest();
    // get HTTP method of current request
    $method = $request->getMethod();
    // get HttpUri object (@see equal\http\HttpUri class for URI structure)
    $uri = $request->getUri();
    // retrieve additional info from URI
    list($path, $route) = [
        $uri->getPath(),       // current URI path
        $uri->get('route')     // 'route' param from URI query string, if any
    ];
    // adjust path to route param, if set
    if($route) {
        $parts = explode(':', $route);
        if(count($parts) > 1) {
            $method = strtoupper($parts[0]);
            $path = $parts[1];
        }
        else {
            $path = $parts[0];
        }
        // remove route param from body, if any
        $request->del('route');
    }

    // 2) handle routing, if required (i.e. URL to operation translation)

    // if routing is required
    if( strlen($path) > 1 && !in_array(basename($path), [
            'index.php',                // HTTP request
            'run.php',                  // CLI
            'equal.php'                 // integration with other frameworks that already use index.php (e.g. WP)
        ]) ) {

        $router = Router::getInstance();
        // add routes providers according to current request
        $router->add(QN_BASEDIR.'/config/routing/*.json');
        $router->add(QN_BASEDIR.'/config/routing/i18n/*.json');
        // translate preflight requests (OPTIONS) to be handled as GET, with announcement
        // (so API does not have to explicitely define OPTIONS routes)
        if($method == 'OPTIONS') {
            $params['announce'] = true;
            $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'TRACE'];
            foreach($methods as $test_method) {
                if($router->resolve($path, $test_method)) {
                    $method = $test_method;
                    break;
                }
            }
        }
        // if route cannot be resolved, raise a "UNKNOWN_OBJECT" exception (HTTP 404)
        if(!($route = $router->resolve($path, $method))) {
            throw new Exception("Unknown route '$method':'$path'", QN_ERROR_UNKNOWN_OBJECT);
        }
        // fetch resolved parameters
        $params = $route['params'];
        // if found URL is another location (absolute notation), redirect to it
        if(isset($route['redirect'])) {
            // resolve params in pointed location, if any
            foreach($params as $param => $value) {
                $route['redirect'] = str_replace(':'.$param, $value, $route['redirect']);
            }
            // remove remaining params and trailing slash, if any
            $route['redirect'] = rtrim(preg_replace('/\/:.+\/?/', '', $route['redirect']), '/');
            // manually set the response header to HTTP 200
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK'); // for HTTP client
            header('Status: 200 OK'); // and CLI
            // redirect to resulting URL
            header('Location: '.$route['redirect']);
            // good job, let's rest now
            exit(0);
        }
        // update current request URI query string with extra params from operation string
        $request->uri()->set($route['operation']['params']);
        // in most cases, parameters from query string will be expected as body parameters
        $params = array_merge($params, $route['operation']['params']);
        // inject resolved parameters into current HTTP request body (if a param is already set, its value is overwritten)
        foreach($params as $key => $value) {
            $request->set($key, $value);
        }
        // now we can keep on processing the request
    }
    else {
        $route = [
            'operation' => Router::normalizeOperation('?'.$uri->query())
        ];
        // if no route is specified in the URI, check for DEFAULT_PACKAGE constant (which might be defined in root config.inc.php)
        if(!isset($route['operation']['name'])) {
            if(defined('DEFAULT_PACKAGE')) {
                $route['operation']['name'] = constant('DEFAULT_PACKAGE');
                $route['operation']['type'] = 'show';
            }
        }
    }

    // 3) perform requested operation and output result to STDOUT
    echo run($route['operation']['type'], $route['operation']['name'], (array) $request->body(), true);
}
// something went wrong: send a HTTP response according to the raised exception
catch(Throwable $e) {
    if( !($e instanceof Exception) ) {
        $error_code = QN_ERROR_UNKNOWN;
    }
    else {
        $error_code = $e->getCode();
    }
    // an exception with code 0 is an explicit request to halt process with no error
    if($error_code != 0) {
        // retrieve info from HTTP request (we don't ask for $context->httpResponse() since it might have raised the current exception)
        $request_method = $context->httpRequestMethod();
        $request_headers = $context->httpRequestHeaders();
        // get HTTP status code according to raised exception
        $http_status = qn_error_http($error_code);
        $http_allow_headers = '*';
        if($request_method == 'OPTIONS') {
            $http_status = 204;
            $http_allow_headers = 'Content-Type';
        }
        // redirect to custom location defined for this code, if any
        if(defined('HTTP_REDIRECT_'.$http_status)) {
            header('Location: '.constant('HTTP_REDIRECT_'.$http_status));
            exit();
        }
        $msg = $e->getMessage();
        // handle serialized objects as message
        $data = @unserialize($msg);
        // retrieve current HTTP response
        $response = $context->httpResponse();
        // adapt response and send it
        $response
        // set HTTP status code
        ->status($http_status)
        // explicitly tell we're returning JSON
        ->header('Content-Type', 'application/json')
        ->header('Content-Disposition', 'inline')
        // force allow-origin to actual origin, to make sure to go through CORS policy
        // (response is defined in announce() and has been unstacked because of an exception)
        ->header('Access-Control-Allow-Origin', $request_headers['Origin'])
        ->header('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD,TRACE')
        ->header('Access-Control-Allow-Headers', $http_allow_headers)
        ->header('Access-Control-Allow-Credentials', 'true')
        // append an 'error' section to response body
        ->extendBody([ 'errors' => [ qn_error_name($error_code) => ($data)?$data:utf8_encode($msg) ] ])
        // for debug purpose
        // ->extendBody([ 'logs' => file_get_contents(QN_LOG_STORAGE_DIR.'/error.log').file_get_contents(QN_LOG_STORAGE_DIR.'/eq_error.log')])
        ->send();
        trigger_error("{$request_headers['Origin']} PHP::".qn_error_name($error_code)." - ".$msg, QN_REPORT_WARNING);
        exit(1);
    }
}