<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageText extends Usage {

    public function getType(): string {
        return 'text';
    }

    public function getConstraints(): array {
        return [
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
                            // #todo - check HTML validity
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

    public function export($value, $lang='en'): string {
        return $value;
    }

}
