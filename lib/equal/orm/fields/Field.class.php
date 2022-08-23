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

    /** @var array */
    private $descriptor = [];

    /** @var mixed */
    private $value = null;

    final public function __construct(array $descriptor) {
        $this->descriptor = $descriptor;
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
        return ($this->hasUsage())?Usages::create($this->descriptor['usage']):null;
    }

    /**
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
     * difference with toJson is that there is a support for the locale
     */
    abstract protected function adaptToTxt($lang=DEFAULT_LANG): void;

    /**
     * Checks if the currently assigned value complies with the type constraints.
     * @throws Exception        In case given value does not comply with targeted type.
     * @return Field     Returns current instance for chained operations.
     */
    abstract public function validate(): Field;
}
