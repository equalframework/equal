<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

class UsageText extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        /*
            text/plain.short (=text/plain:255)
            text/plain.small (65KB)
            text/plain.medium (16MB)
            text/plain.long (4GB)
        */
        // #memo - $this->subtype holds the full tree
        switch($this->getSubtype(0)) {
            case 'plain':
                switch($this->getSubtype(1))  {
                    case 'short':
                        $this->length = 255;
                        break;
                    case 'small':
                        $this->length = 65 * 000;
                        break;
                    case 'medium':
                        $this->length = 16 * 1000 * 1000;
                        break;
                    case 'long':
                        $this->length = 4 * 1000 * 1000 * 1000;
                        break;
                }
                break;
            case 'html':
            case 'json':
            case 'xml':
            case 'wiki':
                $this->length = max($this->length, 65 * 000);
                break;
        }

        if($this->length == 0) {
            $this->length = 255;
        }
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
                    switch($this->getSubtype(0)) {
                        case 'plain':
                            break;
                        case 'html':
                            $doc = new \DOMDocument();
                            libxml_use_internal_errors(true);
                            $doc->loadHTML($value);
                            // discard warnings
                            $filtered_errors = array_filter(libxml_get_errors(), function($error) {
                                // #todo - add constant for strict HTML validation [LIBXML_ERR_WARNING, LIBXML_ERR_ERROR, LIBXML_ERR_FATAL]
                                return in_array($error->level, [LIBXML_ERR_FATAL]);
                            });
                            return empty($filtered_errors);
                            break;
                        case 'json':
                            @json_decode($value);
                            return (json_last_error() === JSON_ERROR_NONE);
                        case 'xml':
                            $xml = new \XMLReader();
                            $xml->xml($value);
                            $xml->setParserProperty(\XMLReader::VALIDATE, true);
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
