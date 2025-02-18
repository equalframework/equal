<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\organic;
use equal\services\Container;

class Service extends Singleton {
    /*  All services are instantiated through the service container
        which instance is, in turn, made available as public member
    */
    public $container;

    /**
     * Returns a list of constant names that the service expects to be available.
     *
     * @return array
     */
    public static function constants() { return []; }


    public function getContainer() {
        return $this->container;
    }

}