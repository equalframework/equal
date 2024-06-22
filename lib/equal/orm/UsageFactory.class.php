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
        UsageTime,
        UsageEmail,
        UsageBinary,
        UsageLanguage,
        UsageNumber,
        UsagePassword,
        UsagePhone,
        UsageText,
        UsageUri,
        UsageArray
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
     * @param string $usage     String representation of the usage (not an Usage instance).
     */
    public static function create(string $usage): Usage {
        // split parts and check usage string consistency
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/?([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?({([0-9]+)(,([0-9]+))?})?/', $usage,  $matches)) {
            throw new \Exception("invalid_usage for $usage", QN_ERROR_INVALID_PARAM);
        }

        /*
            group 1 = type
            group 3 = array size
            group 4 = subtype
            group 6 = subtype tree
            group 8 = length
            group 9 = precision
            group 10 = scale
            group 12 = min
            group 14 = max
        */

        $type = $matches[1];
        // $subtype = $matches[4];

        $usageInstance = null;

        switch($type) {
            case 'number':
                $usageInstance = new UsageNumber($usage);
                break;
            case 'text':
                $usageInstance = new UsageText($usage);
                break;
            case 'date':
                $usageInstance = new UsageDate($usage);
                break;
            case 'time':
                $usageInstance = new UsageTime($usage);
                break;
            case 'array':
                $usageInstance = new UsageArray($usage);
                break;
            case 'binary':
            // #memo - file and image types are deprecated
            case 'file':
            case 'image':
                $usageInstance = new UsageBinary($usage);
                break;

            /* non-generic content-types */
            case 'amount':
                $usageInstance = new UsageAmount($usage);
                break;
            case 'coordinate':
                break;
            case 'country':
                $usageInstance = new UsageCountry($usage);
                break;
            case 'currency':
                $usageInstance = new UsageCurrency($usage);
                break;
            case 'email':
                $usageInstance = new UsageEmail($usage);
                break;
            case 'hash':
                break;
            case 'language':
                $usageInstance = new UsageLanguage($usage);
                break;
            case 'password':
                $usageInstance = new UsagePassword($usage);
                break;
            case 'phone':
                $usageInstance = new UsagePhone($usage);
                break;
            case 'uri':
                $usageInstance = new UsageUri($usage);
                break;
            default:
                $usageInstance = new Usage($usage);
        }
        return $usageInstance;
    }

}
