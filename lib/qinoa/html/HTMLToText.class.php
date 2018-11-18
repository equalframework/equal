<?php
/************************************************************************
*                                                                       *
* Converts HTML to formatted plain text                                 *
* 
*                                                                       *
*                                                                       *
* This script is free software; you can redistribute it and/or modify   *
* it under the terms of the GNU General Public License as published by  *
* the Free Software Foundation; either version 2 of the License, or     *
* (at your option) any later version.                                   *
*                                                                       *
* The GNU General Public License can be found at                        *
* http://www.gnu.org/copyleft/gpl.html.                                 *
*                                                                       *
* This script is distributed in the hope that it will be useful,        *
* but WITHOUT ANY WARRANTY; without even the implied warranty of        *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          *
* GNU General Public License for more details.                          *
*                                                                       *
* Author(s): Cedric Francoys <cedricfrancoys@gmail.com>                 *
*                                                                       *
* Last modified: 11/01/2017                                               *
*                                                                       *
*************************************************************************/
namespace qinoa\html;
use qinoa\html\HTMLPurifier as HTMLPurifier;
use qinoa\html\HTMLPurifier_Config as HTMLPurifier_Config;


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
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');  // use UTF-8
        $config->set('HTML.Allowed', '');        // disallow all tags
        $purifier = new HTMLPurifier($config);
        // remove HTML tags 
        $value = $purifier->purify($value);
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