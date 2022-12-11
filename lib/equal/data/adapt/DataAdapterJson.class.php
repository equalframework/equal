<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterJson extends DataAdapter {

    public function __construct() {
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * x -> PHP
     *
     */
	public function adaptIn($value, $usage) {
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        switch($usage->getType()) {

        }
        return parent::adaptIn($value, $usage);
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * This method is meant to be overloaded by children classes and called as fallback.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage) {
        return parent::adaptOut($value, $usage);
    }

}