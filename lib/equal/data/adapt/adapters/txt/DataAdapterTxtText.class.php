<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt\adapters\txt;

use equal\data\adapt\DataAdapter;
use equal\orm\UsageFactory;
use equal\orm\usages\Usage;
use equal\html\HTMLToText;

class DataAdapterTxtText implements DataAdapter {

    public function getType() {
        return 'txt/text';
    }

    /**
     * Handles the conversion to the PHP type equivalent.
     * Adapts the input value from JSON type to PHP type (JSON -> PHP).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
	public function adaptIn($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            $result = (string) $value;
        }
        return $result;
    }

    /**
     * Handles the conversion to the type targeted by the DataAdapter.
     * Adapts the input value from PHP type to JSON type (PHP -> JSON).
     *
     * @param mixed         $value      Value to be adapted.
     * @param string|Usage  $usage      The usage descriptor the adaptation is requested for.
     * @param string        $locale     The locale to be used for adaptation.
     * @return mixed
     */
    public function adaptOut($value, $usage, $locale='en') {
        $result = null;
        if(!is_null($value)) {
            if(!($usage instanceof Usage)) {
                $usage = UsageFactory::create($usage);
            }
            $subtype = $usage->getSubtype();
            switch($subtype) {
                case 'html':
                    $result = HTMLToText::convert($value, false);
                case 'markdown':
                    // #todo - convert from MD
                case 'wiki':
                    // #todo - convert from wikitext
                default:
                    $result = (string) $value;
            }
        }
        return $result;
    }

}
