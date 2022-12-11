<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;


interface DataAdapterInterface {
    /**
     * Routes the adaptation request to the appropriate method.
     * Conversion: x -> PHP
     *
     * @param mixed         $value
     * @param Usage|string  $usage
     * @return mixed
     */
	public function adaptIn($value, $usage);

    /**
     * Routes the adaptation request to the appropriate method.
     * Conversion: PHP -> x
     *
     * @param mixed         $value
     * @param Usage|string  $usage
     * @return mixed
     */
    public function adaptOut($value, $usage);
}
