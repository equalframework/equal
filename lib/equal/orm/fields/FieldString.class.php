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
        return 'varchar(255)';
    }

    public function getConstraints(): array {
        return array_merge(parent::getConstraints(), [
            'not_string_type' => [
                'message'   => 'Value is not a string.',
                'function'  =>  function($value) {
                    return (gettype($value) == 'string');
                }
            ]
        ]);
    }

    protected function adaptFromSql($value): void {
        $this->value = strval($value);
    }

    protected function adaptFromTxt($value): void {
        // #memo - urldecoding (and any pre-treatment) should not occur here
        $this->value = strval($value);
    }

    protected function adaptToJson(): void {
    }

    protected function adaptToSql(): void {
        // make sure the string does not have a binary notation
        if(substr($this->value, 0, 3) == 'h0x') {
            // trim binary prefix
            $this->value = substr($this->value, 1);
        }
    }

    protected function adaptToTxt($lang=DEFAULT_LANG): void {
        if($this->hasUsage()) {
            $usage = $this->getUsage();
            if($usage) {
                $this->value = $usage->export($this->value, $lang);
            }
        }
    }

}
