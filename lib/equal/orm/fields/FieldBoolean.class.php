<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\locale\Locale;

class FieldBoolean extends Field {

    public function getSqlType(): string {
        return 'tinyint(4)';
    }

    public function validate(): Field {
        if(gettype($this->value) != 'boolean') {
            throw new \Exception(serialize(["not_valid_boolean" => "Value {$this->value} is not integer."]), QN_ERROR_INVALID_PARAM);
        }
        return $this;
    }

    protected function adaptFromSql($value): void {
        $this->value = (bool) (intval($value) > 0);
    }

    protected function adaptFromTxt($value): void {
        $this->value = (in_array($value, ['TRUE', 'true', '1', 1, true], true));
    }

    protected function adaptToJson(): void {
        $this->value = (bool) $this->value;
    }

    protected function adaptToSql(): void {
        $this->value = ($this->value)?'1':'0';
    }

    protected function adaptToTxt($lang=DEFAULT_LANG): void {
        if($this->value) {
            $$this->value = Locale::get_term('core', 'bool.true', 'true', $lang);
        }
        else {
            $this->value = Locale::get_term('core', 'bool.false', 'false', $lang);
        }
    }

}
