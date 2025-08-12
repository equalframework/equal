<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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
    public static function create(string $str_usage): Usage {
        // split parts and check usage string consistency
        if(!preg_match('/([a-z]+)(\[([0-9]+)\])?\/?([-a-z0-9]*)(\.([-a-z0-9.]*))?(:(([-0-9a-z]*)\.?([0-9]*)))?({([0-9]+)(,([0-9]+))?})?/', $str_usage,  $matches)) {
            throw new \Exception("invalid_usage for $str_usage", QN_ERROR_INVALID_PARAM);
        }

        /*
            Syntax: type[size]/subtype.t.r.e.e:precision.scale{min,max}

            group 1 = type          : "type"
            group 3 = size (array)  : "size"
            group 4 = subtype       : "subtype"
            group 6 = subtype tree  : "t.r.e.e"
            group 8 = length        : "precision.scale" or "length"
            group 9 = precision     : "precision"
            group 10 = scale        : "scale"
            group 12 = min          : "min"
            group 14 = max          : "max"
        */

        $type = $matches[1];
        // $subtype = $matches[4];

        $usageInstance = null;

        switch($type) {
            case 'number':
                $usageInstance = new UsageNumber($str_usage);
                break;
            case 'text':
                $usageInstance = new UsageText($str_usage);
                break;
            case 'date':
                $usageInstance = new UsageDate($str_usage);
                break;
            case 'time':
                $usageInstance = new UsageTime($str_usage);
                break;
            case 'array':
                $usageInstance = new UsageArray($str_usage);
                break;
            case 'binary':
            // #deprecated - file shouldn't be used, use 'binary' instead (for files, consider using `documents\Document` entity)
            case 'file':
            // #memo image is an usage of its own, but is handled as a binary
            case 'image':
                $usageInstance = new UsageBinary($str_usage);
                break;
            /* non-generic content-types */
            case 'amount':
                $usageInstance = new UsageAmount($str_usage);
                break;
            case 'coordinate':
            // #todo
                break;
            case 'country':
                $usageInstance = new UsageCountry($str_usage);
                break;
            case 'currency':
                $usageInstance = new UsageCurrency($str_usage);
                break;
            case 'email':
                $usageInstance = new UsageEmail($str_usage);
                break;
            case 'hash':
                break;
            case 'language':
                $usageInstance = new UsageLanguage($str_usage);
                break;
            case 'password':
                $usageInstance = new UsagePassword($str_usage);
                break;
            case 'phone':
                $usageInstance = new UsagePhone($str_usage);
                break;
            case 'uri':
                $usageInstance = new UsageUri($str_usage);
                break;
            default:
                $usageInstance = new Usage($str_usage);
        }
        return $usageInstance;
    }

}
