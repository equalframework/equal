<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\usages\Usage;

class DataAdapterTxt implements DataAdapter {
    /** @var DataAdapterProviderTxt */
    private $dap;

    /** @var string */
    private $type;

    public function __construct() {
        $this->type = 'txt';
        $this->dap = null;
    }

    public function getType() {
        return $this->type;
    }

    public function castInType(): string {
        return '';
    }

    public function castOutType($usage=null): string {
        return '';
    }

    /**
     * Retrieves the appropriate TXT DataAdapter and adapts the input value from TXT type to PHP type (TXT -> PHP).
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
            $this->dap = new DataAdapterProviderTxt();
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
     * Retrieves the appropriate TXT DataAdapter and adapts the input value from TXT type to PHP type (PHP -> TXT).
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        if(is_null($this->dap)) {
            $this->dap = new DataAdapterProviderTxt();
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
