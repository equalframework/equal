<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\json;

use equal\data\adapt\DataAdapter;

class DataAdapterJsonBoolean implements DataAdapter {

    public function getType() {
        return 'json/boolean';
    }

    public function castInType(): string {
        return 'boolean';
    }

    public function castOutType($usage=null): string {
        return 'Boolean';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (JSON -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(in_array($value, ['NULL', 'null', null], true)) {
            $result = null;
        }
        elseif(in_array($value, ['TRUE', 'true', '1', 1, true], true)) {
            $result = true;
        }
        elseif(in_array($value, ['FALSE', 'false', '0', 0, false], true)) {
            $result = false;
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> JSON).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($value)) {
            return null;
        }
        return (bool) $value;
    }

}
