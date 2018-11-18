<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\services;

use qinoa\organic\Service;
use \ReflectionClass;
use \ReflectionException;

class Container extends Service {
    
    private $instances;
    
    /*
    serves as a map to assign aliases to services
    */
    private $register;

    public function __construct() {
        $this->instances = [];
        $this->register = &$GLOBALS['QN_SERVICES_POOL'];
    }
    
    public static function constants() {
        return ['QN_ERROR_UNKNOWN_SERVICE'];
    }
    
    public function register($name='', $class=null) {
        $args = func_get_args();
        if(count($args) < 1) {
            return $this->register;
        }        
        if(is_array($name)) {
            foreach($name as $service => $class) {
                $this->register[$service] = $class;
            }
        }
        else {
            $this->register[$name] = $class;
        }
        return $this;
    }
    
    private function resolve($name) {
        if(in_array($name, array_keys($this->register))) {
            $name = $this->register[$name];
        }
        return $name;
    }
    
    // this is the only place where services are instanciated
    private function inject($dependency) {
        $instance = null;
        $unresolved_dependencies = [];
        try {
            // inspect target class (autoload if required)
            $dependencyClass = new ReflectionClass($dependency);
            $constructor = $dependencyClass->getConstructor();
            $parameters = $constructor->getParameters();    
            
            // check dependencies availability
            $dependencies_instances = [];
            
            if(count($parameters)) {
                foreach($parameters as $parameter) {
                    $constructor_dependancy = $parameter->getClass()->getName();
                    // todo : no cyclic dependency check is done            
                    $res = $this->inject($constructor_dependancy);            
                    if(count($res[1])) {
                        $unresolved_dependencies = array_merge($unresolved_dependencies, $res[1]);
                        continue;
                    }
                    if($res[0] instanceof $constructor_dependancy) {
                        $dependencies_instances[] = $res[0];
                    }
                }
            }
            // check if class owns a getInstance() method
            if(!is_callable($dependency.'::getInstance')) {
                $unresolved_dependencies[] = $dependency;
                trigger_error("required method 'getInstance' is not defined for class '$dependency'", E_USER_WARNING);
            }
            else {
                // check for required constants availability
                $constants = [];
                if(is_callable($dependency.'::constants')) {
                    $constants = call_user_func($dependency.'::constants');
                    foreach($constants as $i => $constant) {
                        if(!defined($constant)) {
                            trigger_error("required constant '$constant' is not defined for service '$dependency'", E_USER_WARNING);
                            break;
                        }
                        unset($constants[$i]);
                    }
                }
                if(count($constants)) {
                    $unresolved_dependencies[] = $dependency;
                }
                // if all dependencies and constants are resolved, instanciate the provider
                if(!count($unresolved_dependencies)) {                    
                    $instance = call_user_func_array($dependency.'::getInstance', $dependencies_instances);
                    // make container available to all services instances
                    $instance->container = $this;
                }
            }
        }
        catch(ReflectionException $e) {
            $unresolved_dependencies[] = $dependency;            
            trigger_error("unable to autoload required dependency '$dependency'", E_USER_WARNING);
        }
        return [$instance, $unresolved_dependencies];            
    }
    
    /**
     *
     *
     * @return mixed (object | array) 
     * @throws Exception
     */
    public function get($name) {
        $instances = [];
        // cast argument as an array
        $names = (array) $name;
        foreach($names as $name) {
            // check for current object request
            if($name == 'container' || $name == __CLASS__) return $this;
            // if no instance is foun, return a null object
            $instance = null;
            $name = $this->resolve($name);
            if(isset($this->instances[$name])) {
                $instance = $this->instances[$name];
            }
            else {
                $res = $this->inject($name);
                if(!count($res[1])) {
                    $instance = $res[0];
                    $this->set($name, $instance);
                }
                // some dependencies are missing
                else {
                    throw new \Exception($name, QN_ERROR_UNKNOWN_SERVICE);   
                }
            }
            // push instance into result array (instance might be null)
            $instances[] = $instance;
        }
        // if argument was a single name, return a single instance
        if(count($names) == 1) {
            $instances = (count($instances))?$instances[0]:false;
        }
        else if(count($names) != count($instances)) {
            $instances = false;
        }
        return $instances;
    }
    
    public function set($name, $instance) {
        $name = $this->resolve($name);
        $this->instances[$name] = $instance;
        return $this;
    }
    

}