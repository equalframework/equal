<?php
namespace qinoa\organic;
use qinoa\services\Container;

class Service extends Singleton {
    /*  All services are instanciated throught the service container
        which instance is, in turn, made available as public member 
    */
    public $container;
    
    /**
     * Returns a list of constant names that the service expect to be available
     *
     * @returns array
     */
    public static function constants() { return []; }
        
}