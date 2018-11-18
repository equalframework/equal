<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\http;

use qinoa\http\HttpUri;


/**
 *
 * Utility class for URI manipulations
 * can be invoked either in a static context
 */ 
class HttpUriHelper {

    private static $instance;

    private static function &getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new HttpUri();
        }
        return self::$instance;        
    }


    public static function __callStatic($method, $arguments) {
        $instance = self::getInstance();
        $instance->setUri($arguments[0]);
        return call_user_func_array(array($instance, $method), []);
    }      
}