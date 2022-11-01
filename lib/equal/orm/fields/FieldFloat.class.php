<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\locale\Locale;

class FieldFloat extends Field {

    public function getSqlType(): string {
        if($this->hasUsage()) {
            return $this->getUsage()->getSqlType();
        }
        return 'decimal(10,2)';
    }

    /**
     * Parent method is in charge of fetching the constraints from the usage, if any.
     */
    public function getConstraints(): array {
        return array_merge(parent::getConstraints(), [
            'not_float_type' => [
                'message'   => 'Value is not a floating point number.',
                'function'  =>  function($value) {
                    return (gettype($value) == 'double');
                }
            ]
        ]);
    }

    protected function adaptFromSql($value): void {
        // #todo : handle numbers of arbitrary length (> 10 digits)
        $this->value = floatval($value);
    }

    protected function adaptFromTxt($value): void {
        if(is_string($value)) {
            if($value == 'null') {
                $value = null;
            }
        }
        // arg represents a numeric value (either numeric type or string)
        if(is_numeric($value)) {
            $value = floatval($value);
        }
        else if(!is_null($value)) {
            throw new \Exception(serialize(["not_valid_float" => "Format inconvertible to float."]), QN_ERROR_INVALID_PARAM);
        }
        $this->value = $value;
    }

    protected function adaptToJson(): void {
    }

    protected function adaptToSql(): void {
    }

    protected function adaptToTxt($lang='en'): void {
        if($this->hasUsage()) {
            $usage = $this->getUsage();
            if($usage) {
                $this->value = $usage->export($this->value, $lang);
            }
        }
        else {
            // get numbers.thousands_separator and numbers.decimal_separator from locale
            $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
            $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);
            $this->value = number_format($this->value, 2, $decimal_separator, $thousands_separator);
        }
    }

}
