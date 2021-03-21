<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\http;

use qinoa\http\HttpHeaders;


/**
 *
 *
 * @usage
 *  HttpHeadersHelper::getCharset($headers);
 *  HttpHeadersHelper::getContentType($headers);
 */
class HttpHeadersHelper {

    private static $instance;

    private static function &getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new HttpHeaders();
        }
        return self::$instance;        
    }
    public static function getCharsets($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getCharsets();
    }    
    public static function getCharset($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getCharset();
    }
    public static function getLanguages($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);
        return $instance->getLanguages();
    }
    public static function getLanguage($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getLanguage();
    }
    public static function getIpAddresses($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getIpAddresses();
    }
    public static function getIpAddress($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getIpAddress();
    }
    public static function getContentType($headers) {
        $instance = self::getInstance();
        $instance->setHeaders($headers);        
        return $instance->getContentType();
    }        
}
