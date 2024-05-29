<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\txt;

use equal\data\adapt\DataAdapter;
use equal\data\DataFormatter;
use equal\locale\Locale;

class DataAdapterTxtDate implements DataAdapter {

    public function getType() {
        return 'txt/date';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType(): string {
        return '';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from TXT type to PHP type (SQL -> PHP).
     * The value is handled as a UTC timestamp or ISO 8601 string.
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        return null;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to TXT type (PHP -> TXT).
     * Converts a timestamp to a TXT date string (Y-m-d).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        $date_format = Locale::get_format('core', 'date.full', 'dddd DD MMMM YYYY', $locale);
        return DataFormatter::format($value, 'date', $date_format);
    }

}
