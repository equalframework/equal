<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\html;
class HTMLToText {

    /**
     * Converts an HTML string to non-formatted text
     *
     * @param $value                string  HTML string (UTF-8)
     * @param $strip_linebreaks     bool    indicates if line breaks are to be preserved
     * @return string
     */
    public static function convert($value, $strip_linebreaks=true) {
        // convert unbreakable spaces to whitespaces
        $value = str_replace(" ", ' ', $value);
        $lineseparator = ($strip_linebreaks)?"\n":' ';

        // create a new DomDocument object
        $doc = new \DOMDocument('1.0', 'utf-8');

        // use htmlentities for all utf-8 characters having an equivalent
        $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
        // load the HTML into the DomDocument object (this would be your source HTML)
        $doc->loadHTML($value);

        self::removeElementsByTagName('script', $doc);
        self::removeElementsByTagName('style', $doc);
        self::removeElementsByTagName('link', $doc);

        // output cleaned html
        $value = $doc->saveHtml();

        // add spaces to closing tags that imply line-return (block nodes)
        $value = preg_replace(['/<br \/>/', '/<hr \/>/', '/<\/h[1-6]>/', '/<\/p>/', '/<\/ul>/', '/<\/ol>/', '/<\/li>/', '/<\/td>/', '/<\/tr>/', '/<\/table>/'], $lineseparator.'\1', $value);
        // remove all HTML (convert to text)
        $value = strip_tags($value);
        if($strip_linebreaks) {
            // strip multiple horizontal whitespaces (preserve carriage returns)
            $value = preg_replace('/\h+/u', ' ', $value);
            // strip redundant linebreaks
            $value = preg_replace('/\n\s*\n\s*\n/', "\n", $value);
        }
        else {
            // strip multiple spaces (including carriage returns)
            $value = preg_replace('/\s+/u', ' ', $value);
        }
        $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
        return $value;
    }

    private static function removeElementsByTagName($tagName, $document) {
        $nodeList = $document->getElementsByTagName($tagName);
        for ($index = $nodeList->length; --$index >= 0; ) {
            $node = $nodeList->item($index);
            $node->parentNode->removeChild($node);
        }
    }

}