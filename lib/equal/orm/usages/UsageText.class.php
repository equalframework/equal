<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;

use equal\data\DataGenerator;

class UsageText extends Usage {

    private const LIBXML_HTML_UNKNOWN_TAG_ERROR_CODE = 801;

    private const LIBXML_LITERAL_AMPERSAND_ERROR_CODES = [
        23, // XML_ERR_ENTITYREF_SEMICOL_MISSING
        68  // XML_ERR_NAME_REQUIRED
    ];

    /**
     * Create an instance and set length according to 'text' specific notations.
     *
     *  text/plain.short (=text/plain:255)
     *  text/plain.small (65KB)
     *  text/plain.medium (16MB)
     *  text/plain.long (4GB)
     *
     * All 'text' usages (text/plain, text/html, text/json, text/xml, text/wiki) are handled the same way
     *
     */
    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            if($this->getType() === 'text') {
                // #memo - $this->subtype holds the full tree
                switch($this->getSubtype(1))  {
                    case 'short':
                        $this->length = 255;
                        break;
                    case 'medium':
                        $this->length = 16 * 1000 * 1000;
                        break;
                    case 'long':
                        $this->length = 4 * 1000 * 1000 * 1000;
                        break;
                    case 'small':
                    default:
                        $this->length = 65 * 1000;
                }
            }
            else {
                $this->length = 255;
            }
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
                    if(empty($value)) {
                        return true;
                    }
                    switch($this->getSubtype(0)) {
                        case 'plain':
                            break;
                        case 'html':
                            return $this->isValidHtml($value);
                        case 'json':
                            return $this->isValidJson($value);
                        case 'xml':
                            return $this->isValidXml($value);
                        case 'markdown':
                            return $this->isValidMarkdown($value);
                        case 'wiki':
                            // #todo - check wikitext validity
                            break;
                    }
                    return true;
                }
            ],
            'unsafe_html' => [
                'message'  => 'HTML contains forbidden elements or attributes.',
                'function' => function($value) {
                    if(empty($value) || $this->getSubtype(0) !== 'html') {
                        return true;
                    }
                    return $this->isSafeHtml($value);
                }
            ]
        ];
    }

    public function generateRandomValue(): string {
        $max = $this->getMax();
        if($max === 0) {
            $max = $this->getLength();
        }

        return DataGenerator::plainText($this->getMin(), $max);
    }

    private function isValidHtml(string $html): bool {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);

        // discard warnings
        $filtered_errors = array_filter(libxml_get_errors(), function($error) {
            // #todo - add constant for strict HTML validation [LIBXML_ERR_WARNING, LIBXML_ERR_ERROR, LIBXML_ERR_FATAL]
            return in_array($error->level, [LIBXML_ERR_FATAL]);
        });
        libxml_clear_errors();

        return empty($filtered_errors);
    }

    private function isValidJson(string $json): bool {
        @json_decode($json);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    private function isValidMarkdown(string $markdown): bool {
        // #todo - check markdown validity
        return true;
    }

    private function isValidXml(string $xml): bool {
        $reader = new \XMLReader();
        $reader->xml($xml);
        $reader->setParserProperty(\XMLReader::VALIDATE, true);
        return $reader->isValid();
    }

    private function isSafeHtml(string $html): bool {
        $tags = array_flip(explode(
            ' ',
            'html body p br strong b em i u s ul ol li blockquote pre code small big h1 h2 h3 h4 h5 h6 a span div hr wbr table thead tbody tfoot tr td th caption colgroup col'
        ));

        $global_attributes = array_flip(['class', 'style']);

        $attributes = [
            'a' => array_flip(['href', 'title', 'rel', 'target'])
        ];

        libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $loaded = $doc->loadHTML(
            '<!DOCTYPE html><html><body>' . $html . '</body></html>',
            LIBXML_NONET
        );

        $errors = array_filter(libxml_get_errors(), function($error) use ($tags) {
            // libxml reports literal ampersands as recoverable entity-reference errors.
            $is_literal_ampersand_error = $error->level !== LIBXML_ERR_FATAL
                && in_array($error->code, self::LIBXML_LITERAL_AMPERSAND_ERROR_CODES, true)
                && strpos(trim($error->message), 'htmlParseEntityRef:') === 0;

            // libxml's HTML4 parser reports some whitelisted HTML5 tags as unknown.
            $is_whitelisted_html5_tag_error = $error->level !== LIBXML_ERR_FATAL
                && $error->code === self::LIBXML_HTML_UNKNOWN_TAG_ERROR_CODE
                && preg_match('/^Tag ([a-z][a-z0-9:-]*) invalid$/i', trim($error->message), $match)
                && isset($tags[strtolower($match[1])]);

            return !$is_literal_ampersand_error && !$is_whitelisted_html5_tag_error;
        });

        $invalid = !$loaded
            || count($errors)
            || $doc->getElementsByTagName('script')->length
            || (new \DOMXPath($doc))->query('//comment()')->length;

        libxml_clear_errors();

        if($invalid) {
            return false;
        }

        foreach($doc->getElementsByTagName('*') as $element) {
            $tag = strtolower($element->nodeName);

            if(!isset($tags[$tag])) {
                return false;
            }

            foreach($element->attributes as $attribute) {
                $name = strtolower($attribute->nodeName);

                if(!isset($global_attributes[$name]) && !isset($attributes[$tag][$name])) {
                    return false;
                }

                if($name === 'href') {
                    $uri = preg_replace('/[\x00-\x20\x7f]+/', '', $attribute->nodeValue);

                    if(
                        preg_match('/^([a-z][a-z0-9+.-]*):/i', $uri, $match)
                        && !in_array(
                            strtolower($match[1]),
                            ['http', 'https', 'mailto', 'tel'],
                            true
                        )
                    ) {
                        return false;
                    }
                }

                if($name === 'rel') {
                    $values = preg_split('/\s+/', strtolower(trim($attribute->nodeValue)), -1, PREG_SPLIT_NO_EMPTY);

                    if(
                        empty($values)
                        || count(array_diff($values, ['noopener', 'noreferrer', 'nofollow']))
                    ) {
                        return false;
                    }
                }

                if(
                    $name === 'target'
                    && !in_array(
                        strtolower(trim($attribute->nodeValue)),
                        ['_blank', '_self', '_parent', '_top'],
                        true
                    )
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
