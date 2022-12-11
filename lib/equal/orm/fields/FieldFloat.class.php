<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\locale\Locale;

class FieldFloat extends Field {

    /**
     * Parent method is in charge of fetching the constraints from the usage, if any.
     */
    public function getConstraints(): array {
        return [
            'not_float_type' => [
                'message'   => 'Value is not a floating point number.',
                'function'  =>  function($value) {
                    return (gettype($value) == 'double');
                }
            ]
        ];
    }

    protected function adaptToTxt($lang='en'): void {
            // get numbers.thousands_separator and numbers.decimal_separator from locale
            $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $lang);
            $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $lang);
            $this->value = number_format($this->value, 2, $decimal_separator, $thousands_separator);

    }

}
