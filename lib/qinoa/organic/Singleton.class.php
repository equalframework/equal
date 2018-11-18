<?php
namespace qinoa\organic;

class Singleton {
    protected function __construct() {}

    protected function __clone() {}

//    public function __sleep() {}
    
//    public function __wakeup() {}

    public static function &getInstance() {
        // late-static-bound class name
        $class_name = get_called_class(); 
        $class_id = '__INSTANCE__'.str_replace("\\", '_', $class_name);
        if (!isset($GLOBALS[$class_id])) {
            $GLOBALS[$class_id] = new $class_name(...func_get_args());
        }
        return $GLOBALS[$class_id];
    }
    
    /**
     * Provide an access to singleton instance in static call context
     *
     */
    public static function _isStatic() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        // retrieve info from where the error was actually raised 
        return $backtrace[1]['type'] == '::';
        
    }    
}