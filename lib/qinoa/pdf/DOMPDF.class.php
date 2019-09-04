<?php
/**
 * This file is an adapter for autoload of qinoa\pdf\DOMPDF
 */
namespace {
    require_once(dirname(realpath(__FILE__)).'/dompdf/dompdf_config.inc.php');    
}
namespace qinoa\pdf {
    // interface class for autoload
    class DOMPDF extends \DOMPDF {
        
        public function __construct($args=null) {
            parent::__construct($args);
        }
        
        public static function __callStatic($name, $arguments) {            
            return call_user_func_array("\DOMPDF::{$name}", $arguments);
        }        
    }    
}