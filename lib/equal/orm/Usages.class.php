<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\orm\usages\UsageCurrency;
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
            // retrieve type
            $type = $parts[0];
            // extract tree
            $def = isset($parts[1])?$parts[1]:'';
            switch($type) {
                case 'amount':
                    return new UsageAmount($def);
                // string usages
                case 'coordinate':
                    break;
                case 'country':
                    return new UsageCountry($def);
                case 'currency':
                    return new UsageCurrency($def);
                // datetime usages
                case 'date':
                    return new UsageDate($def);
                case 'email':
                    return new UsageEmail($def);
                case 'hash':
                    break;
                case 'image':
                    return new UsageImage($def);
                case 'language':
                    return new UsageLanguage($def);
                // number usages
                case 'numeric':
                    return new UsageNumeric($def);
                case 'password':
                    return new UsagePassword($def);
                case 'phone':
                    return new UsagePhone($def);
                case 'text':
                    return new UsageText($def);
                case 'time':
                    break;
                case 'uri':
                    break;
            }
        }
        return null;
    }

}
