<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\txt;

use core\setting\Setting;
use equal\data\adapt\DataAdapter;
use equal\locale\Locale;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

class DataAdapterTxtReal implements DataAdapter {

    public function getType() {
        return 'txt/real';
    }

    public function castInType(): string {
        return 'float';
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
        // arg represents a numeric value (either numeric type or string)
        if(is_numeric($value)) {
            if(!($usage instanceof Usage)) {
                /** @var \equal\orm\usages\Usage */
                $usage = UsageFactory::create($usage);
            }
            $result = round(floatval($value), $usage->getScale());
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
        if(is_null($value)) {
            return null;
        }
        if(!($usage instanceof Usage)) {
            /** @var \equal\orm\usages\Usage */
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();

        if($type == 'amount') {
            return $this->amountToTxt($value, $usage, $locale);
        }
        elseif($type == 'number') {
            return $this->floatToTxt($value, $usage, $locale);
        }

        return number_format($value, $usage->getScale(), '.', '');
    }

    private function floatToTxt($value, $usage, $locale) {
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $locale);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $locale);
        $decimal_length = intval($usage->getScale());
        return number_format($value, $decimal_length, $decimal_separator, $thousands_separator);
    }

    private function amountToTxt($value, $usage, $locale) {
        $decimal_length = intval($usage->getScale());
        // get numbers.thousands_separator and numbers.decimal_separator from locale
        $thousands_separator = Locale::get_format('core', 'numbers.thousands_separator', ',', $locale);
        $decimal_separator = Locale::get_format('core', 'numbers.decimal_separator', '.', $locale);

        $number = number_format($value, $decimal_length, $decimal_separator, $thousands_separator);

        switch($usage->getSubtype()) {
            case 'money':
                $currency_symbol_position = Locale::get_format('core', 'currency.symbol_position', 'before', $locale);
                $currency_symbol_separator = Locale::get_format('core', 'currency.symbol_separator', '', $locale);
                $currency_symbol = Setting::get_value('core', 'units', 'currency', '$');
                if($currency_symbol_position == 'before') {
                    $number = $currency_symbol.$currency_symbol_separator.$number;
                }
                else {
                    $number = $number.$currency_symbol_separator.$currency_symbol;
                }
                break;
            case 'percent':
                $number = $number.'%';
                break;
            case 'rate':
                break;
        }
        return $number;
    }

}
