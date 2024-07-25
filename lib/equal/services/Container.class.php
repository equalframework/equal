<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\services;

use equal\organic\Service;
use \ReflectionClass;
use \ReflectionException;

class Container extends Service {

    private $instances;

    /** @var array  Map for assigning aliases to services. */
    private $register;

    public function __construct() {
        $this->instances = [];
        $this->register = &$GLOBALS['EQ_SERVICES_POOL'];
    }

    public static function constants() {
        return ['EQ_ERROR_UNKNOWN_SERVICE'];
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

    // this is the only place where services are instantiated
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
                    // #memo - ReflectionType::__toString has been deprecated PHP 7.4 and undeprecated in 8.0
                    /** @var ReflectionType */
                    $type_name = @ (string) $parameter->getType();
                    // ignore scalar types
                    if(empty($type_name) || in_array($type_name, ['array', 'bool', 'callable', 'float', 'int', 'null', 'object', 'string', 'false', 'iterable', 'mixed', 'never', 'true', 'void'])) {
                        continue;
                    }
                    if($type_name == 'equal\services\Container') {
                        $dependencies_instances[] = $this;
                    }
                    else {
                        // #todo - add support for cyclic dependency detection
                        $res = $this->inject($type_name);
                        if(count($res[1])) {
                            $unresolved_dependencies = array_merge($unresolved_dependencies, $res[1]);
                            continue;
                        }
                        if($res[0] instanceof $type_name) {
                            $dependencies_instances[] = $res[0];
                        }
                    }
                }
            }
            // check if class owns a getInstance() method
            if(!is_callable($dependency.'::getInstance')) {
                $unresolved_dependencies[] = $dependency;
                trigger_error("Required method 'getInstance' is not defined for class '$dependency'.", QN_REPORT_WARNING);
            }
            else {
                // check for required constants availability
                $constants = [];
                if(is_callable($dependency.'::constants')) {
                    $constants = call_user_func($dependency.'::constants');
                    foreach($constants as $i => $constant) {
                        if(!defined($constant) && \config\defined($constant)) {
                            \config\export($constant);
                        }
                        if(!defined($constant)) {
                            trigger_error("Required constant '$constant' is not defined for service '$dependency'.", QN_REPORT_WARNING);
                            break;
                        }
                        unset($constants[$i]);
                    }
                }
                if(count($constants)) {
                    $unresolved_dependencies[] = $dependency;
                }
                // if all dependencies and constants are resolved, instantiate the provider
                if(!count($unresolved_dependencies)) {
                    $instance = call_user_func_array($dependency.'::getInstance', $dependencies_instances);
                    // make container available to all services instances
                    $instance->container = $this;
                }
            }
        }
        catch(ReflectionException $e) {
            $unresolved_dependencies[] = $dependency;
            trigger_error("Unable to autoload required dependency '$dependency'.", QN_REPORT_WARNING);
        }
        return [$instance, $unresolved_dependencies];
    }

    /**
     *
     * @param  mixed    $name (string | array) name(s) of services to retrieve
     * @return mixed    (object | array)
     * @throws Exception
     */
    public function get($name) {
        $instances = [];
        // cast argument as an array
        $names = (array) $name;
        foreach($names as $name) {
            // check for current object request
            if($name == 'container' || $name == __CLASS__) {
                return $this;
            }
            // if no instance is found, return a null object
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
                    throw new \Exception($name, EQ_ERROR_UNKNOWN_SERVICE);
                }
            }
            // push instance into result array (instance might be null)
            $instances[] = $instance;
        }
        // if argument was a single name, return a single instance
        if(count($names) == 1) {
            $instances = (count($instances))?$instances[0]:false;
        }
        elseif(count($names) != count($instances)) {
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
