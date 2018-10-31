<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

/**
* This script is the root entry point and acts as dispatcher.
* Its role is to set up the context and handle the client request.
* Dispatching consists of resolving targeted operation and include related script file.
*
*
* Usage examples:
* CLI: php run.php --get=public:resiway_tests --id=1 --test=2 --announce=true
* HTTP: /index.php?get=resiway_tests&id=1&test=2
*/
use config\QNLib;
use qinoa\php\Context;
use qinoa\route\Router;


/*
 Load bootstrap library: system constants and functions definitions
 (QN library allows to include required files and classes)
*/
$bootstrap = dirname(__FILE__).'/qn.lib.php';
if( (include($bootstrap)) === false ) die('qinoa lib is missing');

try {
    
    // 1) retrieve current HTTP context
    
    // get PHP context
    $context = Context::getInstance();
    // fetch current HTTP request from context
    $request = $context->getHttpRequest();
    // get HTTP method of current request    
    $method = $request->getMethod();
    // get HttpUri object (@see qinoa\HttpUri class for URI structure)
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
        else $path = $parts[0];
        // remove route param from body, if any
        $request->del('route');
    }

    // 2) handle routing, if required (i.e. URL to operation transaltion)

    // if routing is required
    if( strlen($path) > 1 && !in_array(basename($path), ['index.php', 'run.php']) ) {

        $router = Router::getInstance();
        // add routes providers according to current request
        if($request->isBot()) $router->add(QN_BASE_DIR.'/config/routing/bot/*.json');
        $router->add(QN_BASE_DIR.'/config/routing/*.json');
        $router->add(QN_BASE_DIR.'/config/routing/i18n/*.json');
        // if route cannot be resolved, raise a HTTP 404 exception
        if(!($route = $router->resolve($path, $method))) {        
            throw new Exception("Unknown route '$method':'$path'", QN_ERROR_UNKNOWN_OBJECT);
        }        
        // fetch resolved parameters
        $params = $route['params']; 
        // if found URL is another location(absolute notation), redirect to it
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
            exit();
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
                $route['operation']['name'] = DEFAULT_PACKAGE;
            }
        }        
    }

    // todo: base this on config
    // $context->httpResponse()->header('Access-Control-Allow-Origin', '*');
    
    // 3) perform requested operation
    QNLib::run($route['operation']['type'], $route['operation']['name'], $request->body(), true);
}
// something went wrong: send an HTTP response with code related to the raised exception
catch(Exception $e) {
    // an exception with code 0 is an explicit process halt with no errror
    if($e->getCode() != 0) {
        // retrieve current HTTP response
        $response = $context->httpResponse();
        // set status and body according to raised exception
        $response
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