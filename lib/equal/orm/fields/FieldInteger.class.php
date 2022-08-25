<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\locale\Locale;

class FieldInteger extends Field {

    public function getSqlType(): string {
        if($this->hasUsage()) {
            return $this->getUsage()->getSqlType();
        }
        return 'int(11)';
    }

    public function getConstraints(): array {
        return array_merge(parent::getConstraints(), [
            'not_integer_type' => [
                'message'   => 'Value is not an integer.',
                'function'  =>  function($value) {
                    return (gettype($value) == 'integer');
                }
            ]
        ]);
    }

    protected function adaptFromSql($value): void {
        // #todo : handle numbers of arbitrary length (> 10 digits)
        $this->value = intval($value);
    }

    protected function adaptFromTxt($value): void {
        if(is_string($value)) {
            if($value == 'null') {
                $value = null;
            }
            else if(empty($value)) {
                $value = 0;
            }
            else if(in_array($value, ['TRUE', 'true'])) {
                $value = 1;
            }
            else if(in_array($value, ['FALSE', 'false'])) {
                $value = 0;
            }
        }
        if(is_numeric($value)) {
            $value = intval($value);
        }
        else if(!is_null($value)) {
            $out_value = serialize($value);
            throw new \Exception(serialize(["not_valid_integer" => "Format inconvertible to integer for {$out_value}."]), QN_ERROR_INVALID_PARAM);
        }
        $this->value = $value;
    }

    protected function adaptToJson(): void {
    }

    protected function adaptToSql(): void {
    }

    protected function adaptToTxt($lang=DEFAULT_LANG): void {
        if($this->hasUsage()) {
            $usage = $this->getUsage();
            if($usage) {
                $this->value = $usage->export($this->value, $lang);
            }
        }
        else {
            $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
            $this->value = number_format($this->value, 0, '', $thousands_separator);
        }
    }

}
