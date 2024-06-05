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

class DataAdapterTxtTime implements DataAdapter {

    public function getType() {
        return 'sql/time';
    }

    public function castInType(): string {
        return 'integer';
    }

    public function castOutType($usage=null): string {
        return '';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from TXT type to PHP type (TXT -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            list($hour, $minute, $second) = sscanf($value, "%d:%d:%d");
            $result = ($hour * 3600) + ($minute * 60) + $second;
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to TXT type (PHP -> TXT).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        // convert time to a timestamp relative to current date at midnight
        $time = strtotime('today midnight');
        $time += $value;
        // #todo - add timezone offset
        $time_format = Locale::get_format('core', 'time.medium', 'HH:mm', $locale);
        return DataFormatter::format($time, 'time', $time_format);
    }

}
