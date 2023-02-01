<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

use equal\orm\usages\{
        Usage,
        UsageAmount,
        UsageCountry,
        UsageCurrency,
        UsageDate,
        UsageEmail,
        UsageImage,
        UsageLanguage,
        UsageNumber,
        UsagePassword,
        UsagePhone,
        UsageText
    };


/**
 * Factory for providing Usage instances.
 *
 */
class UsageFactory {

    public function __construct() {
    }

    /**
     * Creates an instance of a Usage object based on a usage string descriptor.
     * @param string $usage_str     String representation of the usage (not an Usage instance).
     */
    public static function create(string $usage_str): Usage {

        // split parts and check usage string consistency
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?/', $usage_str,  $matches)) {
            // error
        }

        /*
            group 1 = type
            group 3 = array size
            group 4 = subtype
            group 8 = length
            group 9 = precision
            group 10 = scale
        */

        $type = $matches[1];
        $subtype = $matches[4];

        switch($type) {
            // string usages
            case 'text':
                return new UsageText($usage_str);
            case 'coordinate':
                break;
            case 'country':
                return new UsageCountry($usage_str);
            case 'currency':
                return new UsageCurrency($usage_str);
            // datetime usages
            case 'date':
                return new UsageDate($usage_str);
            case 'time':
            case 'datetime':
                break;
            case 'email':
                return new UsageEmail($usage_str);
            case 'hash':
                break;
            case 'image':
                return new UsageImage($usage_str);
            case 'language':
                return new UsageLanguage($usage_str);
            // numeric usages
            case 'amount':
                return new UsageAmount($usage_str);
            case 'number':
                return new UsageNumber($usage_str);
            case 'password':
                return new UsagePassword($usage_str);
            case 'phone':
                return new UsagePhone($usage_str);
            case 'time':
                break;
            case 'uri':
                break;
        }
        return null;
    }

}
