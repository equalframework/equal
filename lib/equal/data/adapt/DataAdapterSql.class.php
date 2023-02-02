<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\UsageFactory;
use equal\orm\usages\Usage;

use equal\locale\Locale;

class DataAdapterSql extends DataAdapter {

    public function __construct() {
        $this->type = 'sql';
    }

    /**
     * Routes the adaptation request to the appropriate method.
     * x -> PHP
     *
     */
	public function adaptIn($value, $usage, $lang='en') {
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        switch($type) {
            case 'number':
                switch($subtype) {
                    case 'boolean':
                        return (bool) (intval($value) > 0);
                    case 'natural':
                    case 'integer':
                        return intval($value);
                    case 'hexadecimal':
                        return is_null($value)?null:hexdec($value);
                    case 'real':
                        return floatval($value);
                }
                break;
            case 'text':
                break;
            case 'time':
                return self::sqlToTime($value);
            case 'date':
                switch($subtype) {
                    case 'time':
                        return self::sqlToDatetime($value);
                    case 'plain':
                        return self::sqlToDate($value);
                    case 'year':
                        // date/year:4 (integer 0-9999)
                    case 'month':
                        // date/month	(integer 1-12, ISO-8601)
                        // date/weekday.mon (ISO-8601: 1 to 7, 1 is Monday)
                        // date/weekday.sun (0 to 6, 0 is Sunday)
                        // date/monthday (ISO-8601)
                        // date/yearweek
                        // date/yearday (ISO-8601)
                }
                break;
            case 'image':
            case 'binary':
                return self::sqlToBinary($value);
            case 'many2one':
                // return self::sqlToMany2One($value);
                return (int) $value;
            case 'one2many':
                // should not be occurring: o2m fields are handled in ORM
                break;
            case 'many2many':
                // should not be occurring: m2m fields are handled in ORM
                break;
            case 'array':
                // array is not a type supported by SQL
                break;

        }
        return parent::adaptIn($value, $usage);
        return $this->adaptDefault($value);
    }


    /**
     * Routes the adaptation request to the appropriate method.
     * PHP -> x
     *
     */
    public function adaptOut($value, $usage, $lang='en') {
        if(!($usage instanceof Usage)) {
            $usage = UsageFactory::create($usage);
        }
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        switch($type) {
            case 'amount':
                return self::floatToSql($value, $usage);
            case 'number':
                switch($subtype) {
                    case 'boolean':
                        return self::booleanToSql($value);
                    case 'natural':
                    case 'integer':
                        // nothing to do
                        break 2;
                    case 'hexadecimal':
                        return is_null($value)?null:hexdec($value);
                    case 'real':
                        return self::floatToSql($value, $usage);
                }
                break;
            case 'time':
                return self::timeToSql($value);
            case 'date':
                switch($subtype) {
                    case 'plain':
                        return self::dateToSql($value);
                    case 'year':
                        return intval(date('Y', $value));
                    case 'month':
                        return date('n', $value);
                }
                break;
            case 'datetime':
                return self::datetimeToSql($value, $usage, $lang);
            case 'image':
            case 'binary':
                return self::binaryToSql($value);

        }
        return parent::adaptOut($value, $usage);
    }


    private function sqlToTime($value) {
        list($hour, $minute, $second) = sscanf($value, "%d:%d:%d");
        return ($hour * 3600) + ($minute * 60) + $second;
    }

    /**
     * SQL date
     */
    private function sqlToDate($value) {
        if(is_null($value)) {
            return null;
        }
        // return date as a timestamp
        list($year, $month, $day) = sscanf($value, "%d-%d-%d");
        return mktime(0, 0, 0, $month, $day, $year);
    }

    private function sqlToDatetime($value) {
        if(is_null($value)) {
            return null;
        }
        // return SQL date as a timestamp
        list($year, $month, $day, $hour, $minute, $second) = sscanf($value, "%d-%d-%d %d:%d:%d");
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    private function sqlToBinary($value) {
        return $value;
    }

    private function booleanToSql($value) {
        return ($value)?'1':'0';
    }

    private function timeToSql($value) {
        $hours = (int) ($value / (60*60));
        $minutes = (int) (($value % (60*60)) / 60);
        $seconds = $value % (60);
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    private function dateToSql($value) {
        if(is_null($value)) {
            return 'NULL';
        }
        return date('Y-m-d', $value);
    }

    private function datetimeToSql($value) {
        if(is_null($value)) {
            return 'NULL';
        }
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * Converts a PHP float number to a SQL standard string.
     * As PHP internal representation may imply a decimal part which is not correlated with the type definition (precision.scale), we have to convert it to a string.
     * @param float                  $value  The float number to be converted to an SQL representation.
     * @param equal\orm\usages\Usage $usage  The Usage instance that applies on the value (from schema).
     */
    private function floatToSql($value, $usage) {
        return number_format($value, $usage->getScale(), '.', '');
    }

    /**
     * Converts a binary value to its hexadecimal representation and mark it for being stored as binary.
     * @param string    $value  The binary value to convert.
     */
    private function binaryToSql($value) {
        // #memo - `h0x` prefix is handled in DBManipulator
        return 'h0x'.bin2hex($value);
    }

}