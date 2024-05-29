<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\usages\Usage;

class DataAdapterJson implements DataAdapter {
    /** @var DataAdapterProviderJson */
    private $dap;

    /** @var string */
    private $type;

    public function __construct() {
        $this->type = 'json';
        $this->dap = null;
    }

    public function getType() {
        return $this->type;
    }

    public function castInType(): string {
        return '';
    }

    public function castOutType(): string {
        return '';
    }

    /**
     * Retrieves the appropriate JSON DataAdapter and adapts the input value from JSON type to PHP type (JSON -> PHP).
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        // by convention, all types/values are nullable
        if(is_null($value)) {
            return null;
        }
        if(is_null($this->dap)) {
            $this->dap = new DataAdapterProviderJson();
        }
        if($usage instanceof Usage) {
            $adapter = $this->dap->get($usage->getName());
        }
        else {
            $adapter = $this->dap->get($usage);
        }
        return $adapter->adaptIn($value, $usage, $locale);
    }

    /**
     * Retrieves the appropriate JSON DataAdapter and adapts the input value from JSON type to PHP type (PHP -> JSON).
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($this->dap)) {
            $this->dap = new DataAdapterProviderJson();
        }
        if($usage instanceof Usage) {
            $adapter = $this->dap->get($usage->getName());
        }
        else {
            $adapter = $this->dap->get($usage);
        }
        return $adapter->adaptOut($value, $usage, $locale);
    }

}
