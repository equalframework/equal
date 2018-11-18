<?php
namespace qinoa\orm;

use qinoa\organic\Service;
use qinoa\services\Container;

/**
 *
 *
 *
 */
class Collections extends Service {
  

    /**
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     */
    protected function __construct(Container $container) {
    }
    
    public function create($class) {
        // instanciate a new collection and give it access to available services through the container instance member 
        return new Collection($class, $this->container->get('orm'), $this->container->get('access'), $this->container->get('auth'), $this->container->get('adapt'));
    }
}