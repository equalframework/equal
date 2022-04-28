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
     * @param $value        string  HTML string
     * @param $linebreaks   bool    indicates if line breaks are to be preserved
     * @return string
     */
    public static function convert($value, $linebreaks=true) {
        // convert unbreakable spaces to whitespaces
        $value = str_replace(" ", ' ', $value);
        $lineseparator = ($linebreaks)?"\n":' ';
        // add spaces to closing tags that imply line-return (block nodes)
        $value = preg_replace(['/<br \/>/', '/<hr \/>/', '/<\/h[1-6]>/', '/<\/p>/', '/<\/ul>/', '/<\/ol>/', '/<\/li>/', '/<\/td>/', '/<\/tr>/', '/<\/table>/'], $lineseparator.'\1', $value);
        // remove all HTML (convert to text)
        // remove HTML tags 
        $value = strip_tags($value);
        if($linebreaks) {
            // strip multiple horizontal whitespaces (preserve carriage returns)
            $value = preg_replace('/\h+/u', ' ', $value);
        }
        else {
            // strip multiple spaces (including carriage returns)
            $value = preg_replace('/\s+/u', ' ', $value);            
        }
        return $value;
    }

}