<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageText extends Usage {

    public function getConstraints(): array {
        return [
            'not_string_type' => [
                'message'   => 'Value is not a string.',
                'function'  =>  function($value) {
                    return (gettype($value) == 'string');
                }
            ],
            'size_exceeded' => [
                'message'   => 'String exceeds usage length constraint.',
                'function'  =>  function($value) {
                    $len = intval($this->getLength());
                    if($len && strlen($value) > $len) {
                        return false;
                    }
                    return true;
                }
            ],
            'broken_usage' => [
                'message'   => 'String does not comply with usage format.',
                'function'  =>  function($value) {
                    $len = intval($this->getLength());
                    switch($this->getSubtype()) {
                        case 'plain':
                            break;
                        case 'html':
                            $doc = new DOMDocument();
                            libxml_use_internal_errors(true);
                            $doc->loadHTML($value);
                            return (empty(libxml_get_errors()));
                            break;
                        case 'xml':
                            // #todo - check XML validity
                            $xml = new XMLReader();
                            $xml->xml($value);
                            $xml->setParserProperty(XMLReader::VALIDATE, true);
                            return $xml->isValid();
                        case 'markdown':
                            // #todo - check markdown validity
                            break;
                        case 'wiki':
                            // #todo - check wikitext validity
                            break;
                    }
                    return true;
                }
            ]
        ];
    }

}
