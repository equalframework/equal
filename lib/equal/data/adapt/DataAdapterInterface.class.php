<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;


interface DataAdapterInterface {

    /**
     * Handles the conversion to the PHP type equivalent.
     * Conversion: x -> PHP
     *
     * @param mixed         $value
     * @param Usage|string  $usage
     * @return mixed
     */
	public function adaptIn($value, $usage, $lang='en');

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Conversion: PHP -> x
     *
     * @param mixed         $value
     * @param Usage|string  $usage
     * @return mixed
     */
    public function adaptOut($value, $usage, $lang='en');
}
