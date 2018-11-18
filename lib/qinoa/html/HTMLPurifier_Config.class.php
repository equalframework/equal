<?php
/**
 * This file is an adapter for autoload of qinoa\html\HTMLPurifier_Config
 */
namespace {
    require_once(dirname(realpath(__FILE__)).'/HTMLPurifier/HTMLPurifier/Bootstrap.php');
    require_once(dirname(realpath(__FILE__)).'/HTMLPurifier/HTMLPurifier.autoload.php');
}
namespace qinoa\html {
    // interface class for autoload
    class HTMLPurifier_Config extends \HTMLPurifier_Config {
        
        public function __construct($args) {
            parent::__construct($args);
        }
        
        public static function __callStatic($name, $arguments) {            
            return call_user_func_array("\HTMLPurifier_Config::{$name}", $arguments);
        }        
    }
    
}