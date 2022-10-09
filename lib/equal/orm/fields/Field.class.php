<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\orm\Usages;
use equal\orm\usages\Usage;

abstract class Field {

    /** @var array
     * Descriptor of the field, as returned by Model::getColumns()
     */
    private $descriptor = [];

    /** @var mixed */
    private $value = null;

    /** @var Usage */
    private $usage = null;

    final public function __construct(array $descriptor) {
        $this->descriptor = $descriptor;
    }

    /**
     * Custom clone handler for cloning Usage sub-instance.
     */
    public function __clone() {
        if($this->usage) {
            $this->usage = clone $this->usage;
        }
    }

    abstract public function getSqlType(): string;

    final protected function hasUsage(): bool {
        return isset($this->descriptor['usage']);
    }

    /**
     * Retrun the value as a PHP-typed var.
     * @return mixed
     */
    final public function get() {
        return $this->value;
    }

    final protected function getUsage(): Usage {
        if(is_null($this->usage) && $this->hasUsage()) {
            $this->usage = Usages::create($this->descriptor['usage']);
        }
        return $this->usage;
    }

    /**
     * Adapt the value of the field to the target language.
     * Pseudo language 'txt' is a text-based output that is locale-dependent.
     *
     * @param string $lang      Only affects txt conversions (output is generated according to $lang locale).
     * @return mixed
     * @throws Exception        In case a value is not convertible to the targeted language.
     */
    final public function adapt($to='php', $lang=DEFAULT_LANG): Field {
        switch($to) {
            case 'json':
                $this->adaptToJson();
                break;
            case 'sql':
                $this->adaptToSql();
                break;
            case 'txt':
                $this->adaptToTxt($lang);
                break;
        }
        return $this;
    }

    /**
     * @param string $lang      Only affects txt conversions (output is generated according to $lang locale).
     * @return mixed
     * @throws Exception        In case given value is not compatible with targeted type.
     */
    final public function set($value, $from='php'): Field {
        switch($from) {
            case 'sql':
                $this->adaptFromSql($value);
                break;
            case 'json':
            case 'txt':
                $this->adaptFromTxt($value);
                break;
        }
        return $this;
    }

    /**
     * @throws Exception        In case given value is not compatible with targeted type.
     */
    abstract protected function adaptFromSql($value): void;

    /**
     * value could originate from a CLI (txt) input or from an API call (json)
     * @throws Exception        In case given value is not compatible with targeted type.
     */
    abstract protected function adaptFromTxt($value): void;

    /**
     * @throws Exception        In case given value is not compatible with targeted type.
     */
    abstract protected function adaptToJson(): void;

    /**
     * @throws Exception        In case given value is not compatible with targeted type.
     */
    abstract protected function adaptToSql(): void;

    /**
     * Unlike toJson, this method provides support for the locale.
     */
    abstract protected function adaptToTxt($lang=DEFAULT_LANG): void;

    /**
     * Retrieves all constraints for the field, according to its type.
     * If a usage property is present, related constraints are merge to the result set.
     *
     * @return array
     */
    public function getConstraints(): array {
        $constraints = [];

        if($this->hasUsage()) {
            $usage = $this->getUsage();
            if($usage) {
                $constraints = $usage->getConstraints();
            }
        }
        return $constraints;
    }
}
