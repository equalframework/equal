<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\orm\usages\UsageText;

/**
 * Factory for providing Usage instances.
 *
 */
class Usages {

    public function __construct() {
    }

    public static function create(string $def) {
        $parts = explode('/', $def);
        if(count($parts)) {
            $type = $parts[0];
            $def = isset($parts[1])?$parts[1]:'';
            switch($type) {
                // string usages
                case 'coordinate':
                    break;
                case 'country':
                    break;
                case 'currency':
                    break;
                case 'email':
                    break;
                case 'hash':
                    break;
                case 'language':
                    break;
                case 'uri':
                    break;
                case 'password':
                    break;
                case 'phone':
                    break;
                case 'text':
                    return new UsageText($def);
                // number usages
                case 'numeric':
                    break;
                case 'amount':
                    break;
                // datetime usages
                case 'date':
                    break;
                case 'time':
                    break;
            }
        }
        return null;
    }

}
