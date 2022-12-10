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

    public static function create(string $usage_str) {

        // check usage string consistency
        if(!preg_match('/([a-z]+)\/([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?/', $usage_str,  $matches)) {
            // error
        }

        $type = $matches[1];
        $subtype = $matches[2];
        $tree = isset($matches[4])?$matches[4]:'';
        $length = isset($matches[7])?$matches[7]:'';
        $scale = isset($matches[8])?$matches[8]:'';

        switch($type) {
            case 'amount':
                return new UsageAmount($usage_str);
            // string usages
            case 'coordinate':
                break;
            case 'country':
                return new UsageCountry($usage_str);
            case 'currency':
                return new UsageCurrency($usage_str);
            // datetime usages
            case 'date':
                return new UsageDate($usage_str);
            case 'email':
                return new UsageEmail($usage_str);
            case 'hash':
                break;
            case 'image':
                return new UsageImage($usage_str);
            case 'language':
                return new UsageLanguage($usage_str);
            // number usages
            case 'numeric':
                return new UsageNumeric($usage_str);
            case 'password':
                return new UsagePassword($usage_str);
            case 'phone':
                return new UsagePhone($usage_str);
            case 'text':
                return new UsageText($usage_str);
            case 'time':
                break;
            case 'uri':
                break;
        }

        return null;
    }

}
