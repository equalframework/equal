<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\http;

use equal\http\HttpUri;


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