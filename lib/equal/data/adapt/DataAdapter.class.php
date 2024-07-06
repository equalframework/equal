<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;


interface DataAdapter {

    /**
     * Provides the type of the adapter.
     * @return string
     */
    public function getType();


    /**
     * Returns the equivalent PHP type.
     *
     * @return string
     */
    public function castInType(): string;

    /**
     * Returns the type of the DataAdapter target.
     *
     * @return string
     */
    public function castOutType($usage=null): string;

    /**
     * Handles the conversion to a value using a PHP type.
     * Adapts the input value from external type to PHP type (x -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage (descriptor) the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en');

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to external type (PHP -> x).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage (descriptor) the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en');
}
