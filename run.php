<?php
/**
*    This file is part of the eQual framework.
*    https://github.com/equalframework/equal
*
*    Some Rights Reserved, The eQual Framework, 2010-2024
*    Original Author: Cedric Francoys
*    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
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
use equal\error\Reporter;
use equal\php\Context;
use equal\route\Router;
use equal\services\Container;

/*
   This is the root entry point and acts as dispatcher.
   Its role is to set up the context and handle the client request.
   Dispatching consists of resolving targeted operation and include related script file.

   Usage examples:

     CLI
             equal.run --get=test_hello
     HTTP
             /?get=resiway_tests&id=1&test=2
     PHP
             run('get', 'utils_sql-schema', ['package'=>'core']);
*/

// Bootstrap the library holding system constants and functions definitions and autoload support.
$bootstrap = dirname(__FILE__).'/eq.lib.php';

if( (include($bootstrap)) === false ) {
    die('Great Scott! eQual lib is missing.');
}
// remove PHP signature in prod
if(constant('ENV_MODE') == 'production') {
    header_remove('x-powered-by');
}

// get PHP context
/** @var \equal\php\Context */
$context = Context::getInstance();

try {
    // 1) retrieve current HTTP context

    // fetch current HTTP request from context
    $request = $context->getHttpRequest();
    // retrieve current user
    $auth = Container::getInstance()->get('auth');
    // keep track of the access in the log
    Reporter::errorHandler(EQ_REPORT_SYSTEM, "AAA::".json_encode(['type' => 'auth', 'user_id' => $auth->userId(), 'ip_address' => $request->getHeaders()->getIpAddress()]));
    // get HTTP method of current request
    $method = $request->getMethod();
    // get HttpUri object (@see equal\http\HttpUri class for URI structure)
    $uri = $request->getUri();
    // retrieve additional info from URI
    list($path, $route) = [
        $uri->getPath(),
        $uri->get('route')
    ];

    // 2) handle routing, if required (i.e. URL to operation translation)

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

    // if routing is required
    if( strlen($path) > 1
        && !in_array(basename($path), [
            // HTTP request
            'index.php',
            // CLI
            'run.php',
            // HTTP request with another framework relying on `index.php` (e.g. WP)
            'equal.php'
        ]) ) {
        $router = Router::getInstance();
        // add routes providers according to current request
        $router->add(QN_BASEDIR.'/config/routing/*.json');
        // translate preflight requests (OPTIONS) to be handled as GET, with announcement
        // (so API does not have to explicitly define OPTIONS routes)
        if($method == 'OPTIONS') {
            $params['announce'] = true;
            $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'TRACE'];
            foreach($methods as $method_candidate) {
                if($router->resolve($path, $method_candidate)) {
                    $method = $method_candidate;
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
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
            // add explicit status for non-HTTP context (CLI)
            header('Status: 200 OK');
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
        // if no route is specified in the URI, check for DEFAULT_PACKAGE constant (which might be defined in root `config.json`)
        if(!isset($route['operation']['name'])) {
            if(defined('DEFAULT_PACKAGE')) {
                $route['operation']['name'] = constant('DEFAULT_PACKAGE');
                $route['operation']['type'] = 'show';
            }
        }
    }

    // 3) perform requested operation

    // store http info to access log
    Reporter::errorHandler(EQ_REPORT_INFO, "NET::".json_encode([
                'method'    => $method,
                'uri'       => (string) $uri,
                'headers'   => $request->getHeaders(true),
                'body'      => $request->getBody()
            ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
        );

    // output result to STDOUT
    echo run($route['operation']['type'], $route['operation']['name'], (array) $request->body(), true);

    // store NET info to access log
    Reporter::errorHandler(EQ_REPORT_SYSTEM, "NET::".json_encode([
                'start'     => $_SERVER["REQUEST_TIME_FLOAT"],
                'end'       => microtime(true),
                'ip'        => $request->getHeaders()->getIpAddress()
            ])
        );
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
        Reporter::handleThrowable($e);
        // retrieve info from HTTP request (we don't ask for $context->httpResponse() since it might have raised the current exception)
        $request = $context->getHttpRequest();
        $request_method = $request->getMethod();
        $request_headers = $request->getHeaders(true);
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
            exit(0);
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
            ->extendBody([
                    // #memo - mb_convert_encoding returns an empty string in PHP 8.1.0 (fixed in 8.1.2)
                    'errors' => [ qn_error_name($error_code) => ($data)?$data:mb_convert_encoding($msg, 'UTF-8', mb_list_encodings()) ]
                ])
            ->send();
        trigger_error("PHP::{$request_method} {$request->getUri()} => $http_status ".qn_error_name($error_code).": ".$msg, ($http_status < 500)?EQ_REPORT_WARNING:EQ_REPORT_ERROR);
    }
    // store NET info to access log
    Reporter::errorHandler(EQ_REPORT_SYSTEM, "NET::".json_encode([
                'start'     => $_SERVER["REQUEST_TIME_FLOAT"],
                'end'       => microtime(true),
                'ip'        => $request->getHeaders()->getIpAddress()
            ])
        );
    // an exception with code 0 is an explicit request to halt process with no error
    if($error_code != 0) {
       // return an error code (for compliance under CLI environment)
        exit(1);
    }
}
